import 'package:blue_thermal_printer/blue_thermal_printer.dart';
import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';

import '../helpers/AppTheme.dart';
import '../helpers/thermal_printer.dart';

class PrinterSettings extends StatefulWidget {
  @override
  State<PrinterSettings> createState() => _PrinterSettingsState();
}

class _PrinterSettingsState extends State<PrinterSettings> {
  final ThermalPrinterService _printerService = ThermalPrinterService();

  List<BluetoothDevice> _devices = [];
  bool _thermalEnabled = false;
  bool _isLoading = true;
  bool _isBusy = false;
  PrintRouteType _selectedRoute = PrintRouteType.customer;

  final Map<PrintRouteType, Map<String, String?>> _routePrinters = {
    PrintRouteType.customer: {},
    PrintRouteType.kitchen: {},
    PrintRouteType.bar: {},
  };
  Map<String, String?> _fallbackPrinter = {};

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    final enabled = await _printerService.isThermalEnabled();
    final devices = await _printerService.getBondedDevices();
    final fallback = await _printerService.getFallbackPrinter();

    final customerPrinter =
        await _printerService.getSavedPrinterForRoute(PrintRouteType.customer);
    final kitchenPrinter =
        await _printerService.getSavedPrinterForRoute(PrintRouteType.kitchen);
    final barPrinter =
        await _printerService.getSavedPrinterForRoute(PrintRouteType.bar);

    if (!mounted) return;
    setState(() {
      _thermalEnabled = enabled;
      _devices = devices;
      _routePrinters[PrintRouteType.customer] = customerPrinter;
      _routePrinters[PrintRouteType.kitchen] = kitchenPrinter;
      _routePrinters[PrintRouteType.bar] = barPrinter;
      _fallbackPrinter = fallback;
      _isLoading = false;
    });
  }

  Future<void> _toggleThermal(bool value) async {
    await _printerService.setThermalEnabled(value);
    if (!mounted) return;
    setState(() {
      _thermalEnabled = value;
    });
  }

  Future<void> _assignPrinterToRoute(BluetoothDevice device,
      {PrintRouteType? route}) async {
    final selected = route ?? _selectedRoute;
    setState(() {
      _isBusy = true;
    });

    final connected = await _printerService.ensureConnected(
      preferredDevice: device,
      route: selected,
      allowFallback: true,
    );
    if (connected) {
      await _printerService.savePrinterForRoute(selected, device);
      await _printerService.setThermalEnabled(true);

      if (!mounted) return;
      setState(() {
        _routePrinters[selected] = {
          'name': device.name,
          'address': device.address,
        };
        _thermalEnabled = true;
      });
      Fluttertoast.showToast(msg: '${selected.label} printer assigned');
    } else {
      Fluttertoast.showToast(msg: 'Unable to connect to selected printer');
    }

    if (!mounted) return;
    setState(() {
      _isBusy = false;
    });
  }

  Future<void> _assignFallback(BluetoothDevice device) async {
    setState(() {
      _isBusy = true;
    });

    final connected = await _printerService.ensureConnected(
      preferredDevice: device,
      route: _selectedRoute,
      allowFallback: false,
    );

    if (connected) {
      await _printerService.saveFallbackPrinter(device);
      if (!mounted) return;
      setState(() {
        _fallbackPrinter = {
          'name': device.name,
          'address': device.address,
        };
      });
      Fluttertoast.showToast(msg: 'Fallback printer updated');
    } else {
      Fluttertoast.showToast(msg: 'Could not connect fallback printer');
    }

    if (!mounted) return;
    setState(() {
      _isBusy = false;
    });
  }

  Future<void> _testSelectedRoute() async {
    setState(() {
      _isBusy = true;
    });

    final printed =
        await _printerService.printTestReceipt(route: _selectedRoute);
    Fluttertoast.showToast(
      msg: printed
          ? '${_selectedRoute.label} test receipt printed'
          : 'Test print failed',
    );

    if (!mounted) return;
    setState(() {
      _isBusy = false;
    });
  }

  Widget _routeCard(PrintRouteType route) {
    final routePrinter = _routePrinters[route] ?? {};
    final name = routePrinter['name'] ?? 'Not assigned';
    final address = routePrinter['address'] ?? '-';
    final isActive = route == _selectedRoute;

    return Card(
      elevation: isActive ? 2 : 0,
      color: isActive ? Theme.of(context).colorScheme.primaryContainer : null,
      child: ListTile(
        leading: Icon(
          Icons.print,
          color: isActive ? Theme.of(context).colorScheme.primary : null,
        ),
        title: Text('${route.label} printer'),
        subtitle: Text('$name\n$address'),
        isThreeLine: true,
        trailing: isActive ? const Icon(Icons.check_circle) : null,
        onTap: () {
          setState(() {
            _selectedRoute = route;
          });
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final themeData = Theme.of(context);

    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Printer Setup',
          style: AppTheme.getTextStyle(themeData.textTheme.titleLarge,
              fontWeight: 600),
        ),
      ),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadData,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  Card(
                    child: SwitchListTile(
                      value: _thermalEnabled,
                      title: Text('Enable Bluetooth Thermal Printing'),
                      subtitle: Text(
                          'Prints to the selected route printer, then fallback if unavailable.'),
                      onChanged: _isBusy ? null : _toggleThermal,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    'Printer routes',
                    style: AppTheme.getTextStyle(
                        themeData.textTheme.titleMedium,
                        fontWeight: 700),
                  ),
                  const SizedBox(height: 8),
                  _routeCard(PrintRouteType.customer),
                  _routeCard(PrintRouteType.kitchen),
                  _routeCard(PrintRouteType.bar),
                  const SizedBox(height: 6),
                  Card(
                    child: ListTile(
                      leading: const Icon(Icons.alt_route),
                      title: const Text('Fallback printer'),
                      subtitle: Text(
                          '${_fallbackPrinter['name'] ?? 'Not assigned'}\n${_fallbackPrinter['address'] ?? '-'}'),
                      isThreeLine: true,
                    ),
                  ),
                  const SizedBox(height: 12),
                  FilledButton.tonalIcon(
                    onPressed: _isBusy ? null : _testSelectedRoute,
                    icon: const Icon(Icons.science_outlined),
                    label: Text('Test ${_selectedRoute.label} printer'),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Paired Bluetooth Devices',
                        style: AppTheme.getTextStyle(
                            themeData.textTheme.titleMedium,
                            fontWeight: 700),
                      ),
                      IconButton(
                        onPressed: _isBusy ? null : _loadData,
                        icon: const Icon(Icons.refresh),
                      ),
                    ],
                  ),
                  if (_devices.isEmpty)
                    Card(
                      child: ListTile(
                        title: const Text('No paired devices found'),
                        subtitle: const Text(
                            'Pair printer in Android Bluetooth settings first.'),
                      ),
                    ),
                  ..._devices.map((device) {
                    final selectedAddress =
                        (_routePrinters[_selectedRoute] ?? {})['address'];
                    final isSelected = selectedAddress == device.address;
                    final isFallback =
                        _fallbackPrinter['address'] == device.address;

                    return Card(
                      child: ListTile(
                        leading: Icon(
                          isSelected
                              ? Icons.check_circle
                              : (isFallback
                                  ? Icons.alt_route
                                  : Icons.print_outlined),
                          color: isSelected
                              ? themeData.colorScheme.primary
                              : themeData.colorScheme.onSurface,
                        ),
                        title: Text(device.name ?? 'Unknown printer'),
                        subtitle: Text(device.address ?? ''),
                        trailing: PopupMenuButton<String>(
                          enabled: !_isBusy,
                          onSelected: (value) {
                            if (value == 'assign_route') {
                              _assignPrinterToRoute(device);
                            }
                            if (value == 'assign_fallback') {
                              _assignFallback(device);
                            }
                          },
                          itemBuilder: (context) => [
                            PopupMenuItem(
                              value: 'assign_route',
                              child: Text(
                                  'Assign to ${_selectedRoute.label} route'),
                            ),
                            const PopupMenuItem(
                              value: 'assign_fallback',
                              child: Text('Set as fallback'),
                            ),
                          ],
                        ),
                        onTap: _isBusy
                            ? null
                            : () {
                                _assignPrinterToRoute(device);
                              },
                      ),
                    );
                  }).toList(),
                ],
              ),
            ),
    );
  }
}
