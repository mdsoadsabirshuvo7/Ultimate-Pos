import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

class ContinuousScanner extends StatefulWidget {
  final Future<bool> Function(String barcode) onScan;

  const ContinuousScanner({Key? key, required this.onScan}) : super(key: key);

  @override
  _ContinuousScannerState createState() => _ContinuousScannerState();
}

class _ContinuousScannerState extends State<ContinuousScanner> {
  MobileScannerController cameraController = MobileScannerController();
  bool isProcessing = false;
  String latestScanned = "";
  bool? lastScanSuccess;

  @override
  void dispose() {
    cameraController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final double circleSize = MediaQuery.of(context).size.width * 0.75;

    return Scaffold(
      backgroundColor: Colors.grey[900],
      appBar: AppBar(
        title: const Text('Scan Products'),
        backgroundColor: Colors.black87,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () {
            Navigator.pop(context);
          },
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.flash_on, color: Colors.yellow),
            onPressed: () => cameraController.toggleTorch(),
          ),
        ],
      ),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: circleSize,
              height: circleSize,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(
                  color: lastScanSuccess == true 
                      ? Colors.green 
                      : (lastScanSuccess == false ? Colors.red : Colors.white24),
                  width: 6,
                ),
                boxShadow: const [
                  BoxShadow(color: Colors.black54, blurRadius: 20, spreadRadius: 5)
                ],
              ),
              child: ClipOval(
                child: MobileScanner(
                  controller: cameraController,
                  onDetect: (capture) async {
                    if (isProcessing) return;

                    final List<Barcode> barcodes = capture.barcodes;
                    for (final barcode in barcodes) {
                      if (barcode.rawValue != null) {
                        setState(() {
                          isProcessing = true;
                          latestScanned = barcode.rawValue!;
                          lastScanSuccess = null;
                        });

                        bool success = await widget.onScan(barcode.rawValue!);

                        setState(() {
                          lastScanSuccess = success;
                        });

                        await Future.delayed(const Duration(milliseconds: 1500));

                        if (mounted) {
                          setState(() {
                            isProcessing = false;
                          });
                        }
                        break;
                      }
                    }
                  },
                ),
              ),
            ),
            
            const SizedBox(height: 40),
            
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
              decoration: BoxDecoration(
                color: Colors.black,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.white12),
              ),
              child: Column(
                children: [
                  if (lastScanSuccess == null && latestScanned.isEmpty)
                     const Text(
                        "Align barcode inside the circle",
                        style: TextStyle(color: Colors.white70, fontSize: 16),
                     ),
                  
                  if (lastScanSuccess == true) ...[
                    const Icon(Icons.check_circle, color: Colors.green, size: 40),
                    const SizedBox(height: 8),
                    Text(
                      "Success!\n$latestScanned",
                      textAlign: TextAlign.center,
                      style: const TextStyle(color: Colors.greenAccent, fontSize: 16, fontWeight: FontWeight.bold),
                    ),
                  ],
                  
                  if (lastScanSuccess == false) ...[
                    const Icon(Icons.error, color: Colors.red, size: 40),
                    const SizedBox(height: 8),
                    Text(
                      "Not Found/Failed!\n$latestScanned",
                      textAlign: TextAlign.center,
                      style: const TextStyle(color: Colors.redAccent, fontSize: 16, fontWeight: FontWeight.bold),
                    ),
                  ],
                  
                  if (isProcessing && lastScanSuccess == null) ...[
                     const CircularProgressIndicator(color: Colors.white),
                     const SizedBox(height: 8),
                     const Text("Sending...", style: TextStyle(color: Colors.white54)),
                  ]
                ],
              ),
            )
          ],
        ),
      ),
    );
  }
}
