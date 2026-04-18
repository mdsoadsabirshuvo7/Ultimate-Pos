import 'dart:convert';
import 'dart:typed_data';

import 'package:blue_thermal_printer/blue_thermal_printer.dart';
import 'package:esc_pos_utils_plus/esc_pos_utils_plus.dart';
import 'package:image/image.dart' as img;
import 'package:permission_handler/permission_handler.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../models/paymentDatabase.dart';
import '../models/sellDatabase.dart';
import 'otherHelpers.dart';

enum PrintRouteType { customer, kitchen, bar }

extension PrintRouteTypeValue on PrintRouteType {
  String get key {
    switch (this) {
      case PrintRouteType.customer:
        return 'customer';
      case PrintRouteType.kitchen:
        return 'kitchen';
      case PrintRouteType.bar:
        return 'bar';
    }
  }

  String get label {
    switch (this) {
      case PrintRouteType.customer:
        return 'Customer';
      case PrintRouteType.kitchen:
        return 'Kitchen';
      case PrintRouteType.bar:
        return 'Bar';
    }
  }
}

class ThermalPrinterService {
  static const String _printerAddressKey = 'thermal_printer_address';
  static const String _printerNameKey = 'thermal_printer_name';
  static const String _printerRouteMapKey = 'thermal_printer_route_map';
  static const String _fallbackPrinterAddressKey =
      'thermal_printer_fallback_address';
  static const String _fallbackPrinterNameKey = 'thermal_printer_fallback_name';
  static const String _thermalEnabledKey = 'thermal_print_enabled';

  final BlueThermalPrinter _bluetooth = BlueThermalPrinter.instance;

  Future<void> requestPermissions() async {
    await [
      Permission.location,
      Permission.bluetoothScan,
      Permission.bluetoothConnect,
    ].request();
  }

  Future<List<BluetoothDevice>> getBondedDevices() async {
    await requestPermissions();
    try {
      return await _bluetooth.getBondedDevices();
    } catch (_) {
      return [];
    }
  }

  Future<void> setThermalEnabled(bool enabled) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_thermalEnabledKey, enabled);
  }

  Future<bool> isThermalEnabled() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getBool(_thermalEnabledKey) ?? false;
  }

  Future<void> savePrinter(BluetoothDevice device) async {
    await savePrinterForRoute(PrintRouteType.customer, device);
  }

  Future<Map<String, String?>> getSavedPrinter() async {
    return getSavedPrinterForRoute(PrintRouteType.customer);
  }

  Future<void> savePrinterForRoute(
      PrintRouteType route, BluetoothDevice device) async {
    final prefs = await SharedPreferences.getInstance();
    final routeMap = await _loadRouteMap(prefs);

    routeMap[route.key] = {
      'name': device.name ?? 'Thermal Printer',
      'address': device.address ?? '',
    };
    await prefs.setString(_printerRouteMapKey, jsonEncode(routeMap));

    // Keep backward-compatible keys synced to customer route.
    if (route == PrintRouteType.customer) {
      await prefs.setString(_printerAddressKey, device.address ?? '');
      await prefs.setString(_printerNameKey, device.name ?? 'Thermal Printer');
    }
  }

  Future<Map<String, String?>> getSavedPrinterForRoute(
      PrintRouteType route) async {
    final prefs = await SharedPreferences.getInstance();
    final routeMap = await _loadRouteMap(prefs);
    final routeDetails = routeMap[route.key] ?? {};

    // Fallback to legacy single-printer keys for customer route.
    if (route == PrintRouteType.customer && routeDetails.isEmpty) {
      return {
        'name': prefs.getString(_printerNameKey),
        'address': prefs.getString(_printerAddressKey),
      };
    }

    return {
      'name': routeDetails['name'],
      'address': routeDetails['address'],
    };
  }

  Future<Map<String, Map<String, String>>> getSavedPrinterRoutes() async {
    final prefs = await SharedPreferences.getInstance();
    final routeMap = await _loadRouteMap(prefs);
    return routeMap.map((k, v) => MapEntry(k, Map<String, String>.from(v)));
  }

  Future<void> saveFallbackPrinter(BluetoothDevice device) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_fallbackPrinterAddressKey, device.address ?? '');
    await prefs.setString(
        _fallbackPrinterNameKey, device.name ?? 'Fallback Printer');
  }

  Future<Map<String, String?>> getFallbackPrinter() async {
    final prefs = await SharedPreferences.getInstance();
    return {
      'name': prefs.getString(_fallbackPrinterNameKey),
      'address': prefs.getString(_fallbackPrinterAddressKey),
    };
  }

  Future<BluetoothDevice?> _findDeviceByAddress(String? address) async {
    if (address == null || address.isEmpty) {
      return null;
    }

    final devices = await getBondedDevices();
    for (final device in devices) {
      if (device.address == address) {
        return device;
      }
    }
    return null;
  }

  Future<BluetoothDevice?> getSavedBondedDevice(
      {PrintRouteType route = PrintRouteType.customer,
      bool allowFallback = true}) async {
    final saved = await getSavedPrinterForRoute(route);
    var target = await _findDeviceByAddress(saved['address']);
    if (target != null) {
      return target;
    }

    if (allowFallback) {
      final fallback = await getFallbackPrinter();
      target = await _findDeviceByAddress(fallback['address']);
      if (target != null) {
        return target;
      }
    }

    return null;
  }

  Future<bool> ensureConnected(
      {BluetoothDevice? preferredDevice,
      PrintRouteType route = PrintRouteType.customer,
      bool allowFallback = true}) async {
    await requestPermissions();
    try {
      final isConnected = (await _bluetooth.isConnected) == true;
      if (isConnected) {
        return true;
      }

      final attempts = <BluetoothDevice>[];
      if (preferredDevice != null) {
        attempts.add(preferredDevice);
      }

      final routeDevice =
          await getSavedBondedDevice(route: route, allowFallback: false);
      if (routeDevice != null) {
        attempts.add(routeDevice);
      }

      if (allowFallback) {
        final fallback = await getFallbackPrinter();
        final fallbackDevice = await _findDeviceByAddress(fallback['address']);
        if (fallbackDevice != null) {
          attempts.add(fallbackDevice);
        }
      }

      for (final target in attempts) {
        try {
          await _bluetooth.connect(target);
          if ((await _bluetooth.isConnected) == true) {
            return true;
          }
        } catch (_) {}
      }

      return false;
    } catch (_) {
      return false;
    }
  }

  Future<bool> printTestReceipt(
      {PrintRouteType route = PrintRouteType.customer,
      BluetoothDevice? preferredDevice}) async {
    final connected = await ensureConnected(
        preferredDevice: preferredDevice, route: route, allowFallback: true);
    if (!connected) {
      return false;
    }

    final profile = await CapabilityProfile.load();
    final generator = Generator(PaperSize.mm58, profile);
    final bytes = <int>[];

    bytes.addAll([27, 112, 0, 25, 250]); // Cash Drawer Kick

    bytes.addAll(generator.text('Sector71 POS',
        styles: PosStyles(
            align: PosAlign.center,
            bold: true,
            width: PosTextSize.size2,
            height: PosTextSize.size2)));
    bytes.addAll(generator.text('${route.label} printer connected',
        styles: PosStyles(align: PosAlign.center)));
    bytes.addAll(generator.text('Test print successful',
        styles: PosStyles(align: PosAlign.center, bold: true)));
    bytes.addAll(generator.feed(2));
    bytes.addAll(generator.cut());

    await _bluetooth.writeBytes(Uint8List.fromList(bytes));
    return true;
  }

  Future<bool> printSaleReceipt({
    required int sellId,
    required int taxId,
    PrintRouteType route = PrintRouteType.customer,
  }) async {
    if (!await isThermalEnabled()) {
      return false;
    }

    final connected = await ensureConnected(route: route, allowFallback: true);
    if (!connected) {
      return false;
    }

    final bytes = await _buildSaleReceiptBytes(
        sellId: sellId, taxId: taxId, route: route);
    if (bytes.isEmpty) {
      return false;
    }

    await _bluetooth.writeBytes(Uint8List.fromList(bytes));
    return true;
  }

  Future<List<int>> _buildSaleReceiptBytes(
      {required int sellId,
      required int taxId,
      required PrintRouteType route}) async {
    final sellList = await SellDatabase().getSellBySellId(sellId);
    if (sellList.isEmpty) {
      return [];
    }

    final sell = sellList.first;
    final items = await SellDatabase().get(sellId: sellId);
    final payments = await PaymentDatabase().get(sellId, allColumns: true);
    final business = await Helper().getFormattedBusinessDetails();

    final profile = await CapabilityProfile.load();
    final generator = Generator(PaperSize.mm58, profile);
    final bytes = <int>[];

    // -- Cash drawer kick command --
    bytes.addAll([27, 112, 0, 25, 250]);

    // -- Base64 Logo Header --
    if (business['logo'] != null && business['logo'].toString().isNotEmpty) {
      try {
        final logoStr = business['logo'].toString();
        // Remove mime prefix if it exists
        final cleanBase64 = logoStr.contains(',') ? logoStr.split(',').last : logoStr;
        final decodedBytes = base64Decode(cleanBase64.replaceAll(RegExp(r'\s+'), ''));
        final image = img.decodeImage(decodedBytes);
        if (image != null) {
          final resized = img.copyResize(image, width: 250); 
          bytes.addAll(generator.image(resized));
        }
      } catch (e) {
        // Ignore logo parsing errors
      }
    }

    bytes.addAll(generator.text((business['name'] ?? 'Receipt').toString(),
        styles: PosStyles(
            align: PosAlign.center,
            bold: true,
            width: PosTextSize.size2,
            height: PosTextSize.size2)));
    bytes.addAll(generator.text('${route.label.toUpperCase()} TICKET',
        styles: PosStyles(align: PosAlign.center, bold: true)));
    bytes.addAll(generator.text('Invoice: ${sell['invoice_no'] ?? sellId}',
        styles: PosStyles(align: PosAlign.center)));
    bytes.addAll(generator.text(
        'Date: ${(sell['transaction_date'] ?? '').toString().split('.').first}',
        styles: PosStyles(align: PosAlign.center)));
    bytes.addAll(generator.hr());

    for (final item in items) {
      final itemName =
          (item['name'] ?? item['display_name'] ?? 'Item').toString();
      final qty = _toDouble(item['quantity']);
      final unitPrice = _toDouble(item['unit_price']);
      final lineTotal = qty * unitPrice;

      bytes.addAll(generator.text(itemName, styles: PosStyles(bold: true)));
      bytes.addAll(generator.row([
        PosColumn(
          text: '${_quantity(qty)} x ${_money(unitPrice)}',
          width: 7,
        ),
        PosColumn(
            text: _money(lineTotal),
            width: 5,
            styles: PosStyles(align: PosAlign.right)),
      ]));
    }

    final paidTotal = payments
        .where((p) => (p['is_return'] ?? 0) == 0)
        .fold<double>(0, (sum, p) => sum + _toDouble(p['amount']));

    bytes.addAll(generator.hr());
    bytes.addAll(generator.row([
      PosColumn(text: 'Total', width: 6, styles: PosStyles(bold: true)),
      PosColumn(
          text: _money(sell['invoice_amount']),
          width: 6,
          styles: PosStyles(align: PosAlign.right, bold: true)),
    ]));
    bytes.addAll(generator.row([
      PosColumn(text: 'Paid', width: 6),
      PosColumn(
          text: _money(paidTotal),
          width: 6,
          styles: PosStyles(align: PosAlign.right)),
    ]));
    bytes.addAll(generator.row([
      PosColumn(text: 'Balance', width: 6),
      PosColumn(
          text: _money(sell['pending_amount']),
          width: 6,
          styles: PosStyles(align: PosAlign.right)),
    ]));

    bytes.addAll(generator.feed(1));
    bytes.addAll(generator.text('Thank you!',
        styles: PosStyles(align: PosAlign.center, bold: true)));
    bytes.addAll(generator.feed(2));
    bytes.addAll(generator.cut());

    return bytes;
  }

  String _quantity(double value) {
    if (value == value.roundToDouble()) {
      return value.toInt().toString();
    }
    return value.toStringAsFixed(2);
  }

  double _toDouble(dynamic value) {
    if (value == null) {
      return 0;
    }
    if (value is num) {
      return value.toDouble();
    }
    return double.tryParse(value.toString()) ?? 0;
  }

  String _money(dynamic value) {
    return _toDouble(value).toStringAsFixed(2);
  }

  Future<Map<String, Map<String, String>>> _loadRouteMap(
      SharedPreferences prefs) async {
    final raw = prefs.getString(_printerRouteMapKey);
    if (raw == null || raw.isEmpty) {
      return {};
    }
    try {
      final decoded = jsonDecode(raw);
      if (decoded is! Map) {
        return {};
      }

      return decoded.map<String, Map<String, String>>((key, value) {
        if (value is Map) {
          final mapValue = value.map<String, String>(
              (k, v) => MapEntry(k.toString(), v.toString()));
          return MapEntry(key.toString(), mapValue);
        }
        return MapEntry(key.toString(), {});
      });
    } catch (_) {
      return {};
    }
  }
}
