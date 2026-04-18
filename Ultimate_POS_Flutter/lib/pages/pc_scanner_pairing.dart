import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import '../config.dart';
import 'continuous_scanner.dart';

class PcScannerPairing extends StatefulWidget {
  @override
  _PcScannerPairingState createState() => _PcScannerPairingState();
}

class _PcScannerPairingState extends State<PcScannerPairing> {
  final TextEditingController _pairingCodeController = TextEditingController();

  void _startScanning() {
    String code = _pairingCodeController.text.trim();
    if (code.isEmpty || code.length < 4) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter a valid 4-digit pairing code from your PC.')),
      );
      return;
    }

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => ContinuousScanner(
          onScan: (barcode) async {
            return await _sendBarcodeToPC(code, barcode);
          },
        ),
      ),
    );
  }

  Future<bool> _sendBarcodeToPC(String pairingCode, String barcode) async {
    try {
      final response = await http.post(
        Uri.parse('${Config.serverOrigin}/api/mobile-scanner/send'),
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({
          'pairing_code': pairingCode,
          'barcode': barcode,
        }),
      );
      
      final data = jsonDecode(response.body);
      return data['success'] == true;
    } catch (e) {
      return false;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Connect PC Scanner'),
        backgroundColor: Colors.blueGrey[900],
      ),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.computer, size: 80, color: Colors.blueGrey),
              const SizedBox(height: 20),
              const Text(
                'Link Phone to PC',
                style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 10),
              const Text(
                'Enter the 4-digit Pairing Code shown on your Sector71 Dashboard to use this phone as a wireless barcode scanner.',
                textAlign: TextAlign.center,
                style: TextStyle(color: Colors.grey),
              ),
              const SizedBox(height: 40),
              TextField(
                controller: _pairingCodeController,
                keyboardType: TextInputType.number,
                maxLength: 4,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 32, letterSpacing: 8, fontWeight: FontWeight.bold),
                decoration: const InputDecoration(
                  hintText: '0000',
                  border: OutlineInputBorder(),
                  focusedBorder: OutlineInputBorder(
                    borderSide: BorderSide(color: Colors.blueGrey, width: 2),
                  ),
                ),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.blueGrey[900],
                  ),
                  onPressed: _startScanning,
                  child: const Text('Connect & Start Scanning', style: TextStyle(fontSize: 18)),
                ),
              )
            ],
          ),
        ),
      ),
    );
  }
}
