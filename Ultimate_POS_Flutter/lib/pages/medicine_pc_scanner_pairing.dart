import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import '../config.dart';
import 'medicine_ai_scanner.dart';

class MedicinePcScannerPairing extends StatefulWidget {
  @override
  _MedicinePcScannerPairingState createState() => _MedicinePcScannerPairingState();
}

class _MedicinePcScannerPairingState extends State<MedicinePcScannerPairing> {
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
        builder: (context) => MedicineAiScanner(
          onDetect: (name, dosage) async {
            // Combine name and dosage to send to the PC's active text field
            String combinedText = "$name $dosage";
            return await _sendTextToPC(code, combinedText);
          },
        ),
      ),
    );
  }

  Future<bool> _sendTextToPC(String pairingCode, String detectedText) async {
    try {
      final response = await http.post(
        Uri.parse("${Config.serverOrigin}/api/mobile-scanner/send"),
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({
          'pairing_code': pairingCode,
          'barcode': detectedText, // Send AI text here
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
        title: const Text('Connect AI Scanner'),
        backgroundColor: Colors.teal[900],
      ),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.document_scanner, size: 80, color: Colors.teal),
              const SizedBox(height: 20),
              const Text(
                'Link AI Scanner to PC',
                style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 10),
              const Text(
                'Enter the 4-digit Pairing Code from your Dashboard to beam medicine names automatically to the PC.',
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
                    borderSide: BorderSide(color: Colors.teal, width: 2),
                  ),
                ),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.teal[900],
                  ),
                  onPressed: _startScanning,
                  child: const Text('Connect AI Scanner', style: TextStyle(fontSize: 18, color: Colors.white)),
                ),
              )
            ],
          ),
        ),
      ),
    );
  }
}