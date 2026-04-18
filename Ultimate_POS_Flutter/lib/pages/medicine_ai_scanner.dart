import 'dart:io';
import 'package:camera/camera.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:google_mlkit_text_recognition/google_mlkit_text_recognition.dart';

class MedicineAiScanner extends StatefulWidget {
  final Future<bool> Function(String name, String dosage) onDetect;

  const MedicineAiScanner({Key? key, required this.onDetect}) : super(key: key);

  @override
  _MedicineAiScannerState createState() => _MedicineAiScannerState();
}

class _MedicineAiScannerState extends State<MedicineAiScanner> {
  CameraController? _cameraController;
  final TextRecognizer _textRecognizer = TextRecognizer(script: TextRecognitionScript.latin);
  bool _isBusy = false;
  Color _borderColor = Colors.blueGrey;
  String _detectedText = 'Scanning...';

  @override
  void initState() {
    super.initState();
    _initializeCamera();
  }

  Future<void> _initializeCamera() async {
    final cameras = await availableCameras();
    final firstCamera = cameras.first;

    _cameraController = CameraController(
      firstCamera,
      ResolutionPreset.high,
      enableAudio: false,
      imageFormatGroup: Platform.isAndroid ? ImageFormatGroup.nv21 : ImageFormatGroup.bgra8888,
    );

    await _cameraController?.initialize();
    if (!mounted) return;

    _cameraController?.startImageStream(_processCameraImage);
    setState(() {});
  }

  Future<void> _processCameraImage(CameraImage image) async {
    if (_isBusy) return;
    _isBusy = true;

    try {
      final inputImage = _inputImageFromCameraImage(image);
      if (inputImage == null) {
        debugPrint('SCAN_LOG: inputImage returned null. Format: ${image.format.group}');
        _isBusy = false;
        return;
      }

      final recognizedText = await _textRecognizer.processImage(inputImage);
      debugPrint('SCAN_LOG: Recognized ${recognizedText.text.length} characters');
      _extractMedicineInfo(recognizedText.text);
    } catch (e) {
      debugPrint('SCAN_LOG Error processing image: $e');
    }

    _isBusy = false;
  }

  InputImage? _inputImageFromCameraImage(CameraImage image) {
    if (_cameraController == null) return null;
    final camera = _cameraController!.description;
    final sensorOrientation = camera.sensorOrientation;

    InputImageRotation? rotation;
    if (Platform.isIOS) {
      rotation = InputImageRotationValue.fromRawValue(sensorOrientation);
    } else if (Platform.isAndroid) {
      var rotationCompensation = sensorOrientation;
      if (camera.lensDirection == CameraLensDirection.front) {
        rotationCompensation = (sensorOrientation + 0) % 360;
      }
      rotation = InputImageRotationValue.fromRawValue(rotationCompensation);
    }
    if (rotation == null) return null;

    final format = InputImageFormatValue.fromRawValue(image.format.raw);
    if (format == null || (Platform.isAndroid && format != InputImageFormat.nv21 && format != InputImageFormat.yuv_420_888)) return null;

    if (image.planes.isEmpty) return null;

    final WriteBuffer allBytes = WriteBuffer();
    for (final Plane plane in image.planes) {
      allBytes.putUint8List(plane.bytes);
    }
    final bytes = allBytes.done().buffer.asUint8List();

    return InputImage.fromBytes(
      bytes: bytes,
      metadata: InputImageMetadata(
        size: Size(image.width.toDouble(), image.height.toDouble()),
        rotation: rotation,
        format: format,
        bytesPerRow: image.planes[0].bytesPerRow,
      ),
    );
  }

  void _extractMedicineInfo(String text) async {
    if (text.trim().isEmpty) return;

    setState(() {
      _detectedText = text.replaceAll('\n', ' ').trim();
      if (_detectedText.length > 50) {
        _detectedText = _detectedText.substring(0, 50) + '...';
      }
    });

    final dosageRegex = RegExp(r'\\b(\\d+(?:\\.\\d+)?\\s*(mg|ml|g|mcg))\\b', caseSensitive: false);
    final match = dosageRegex.firstMatch(text);

    String name = 'Unknown';
    String dosage = '';
    final lines = text.split('\n');

    if (match != null) {
      dosage = match.group(1)!;

      for (var line in lines) {
        if (line.contains(dosage)) break;
        if (line.trim().length >= 3) {
          name = line.trim();
        }
      }
    } else {
      for (var line in lines) {
        if (line.trim().length >= 4) {
          name = line.trim();
          break;
        }
      }
    }

    if (name != 'Unknown') {
      _cameraController?.stopImageStream();
      bool success = await widget.onDetect(name, dosage);

      setState(() {
        _borderColor = success ? Colors.green : Colors.red;
      });

      await Future.delayed(const Duration(seconds: 2));

      setState(() {
        _borderColor = Colors.blueGrey;
        _detectedText = 'Scanning...';
      });

      if (mounted) {
        _cameraController?.startImageStream(_processCameraImage);
      }
    }
  }

  @override
  void dispose() {
    _cameraController?.dispose();
    _textRecognizer.close();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (_cameraController == null || !_cameraController!.value.isInitialized) {
      return const Scaffold(
        backgroundColor: Colors.black,
        body: Center(child: CircularProgressIndicator()),
      );
    }

    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        alignment: Alignment.center,
        children: [
          Center(
            child: ClipOval(
              child: SizedBox(
                width: 300,
                height: 300,
                child: CameraPreview(_cameraController!),
              ),
            ),
          ),
          Center(
            child: Container(
              width: 300,
              height: 300,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(color: _borderColor, width: 4),
              ),
            ),
          ),
          Positioned(
            bottom: 100,
            child: Text(
              _detectedText,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 24,
                fontWeight: FontWeight.bold,
                backgroundColor: Colors.black54,
              ),
            ),
          ),
          Positioned(
            top: 40,
            right: 20,
            child: IconButton(
              icon: const Icon(Icons.close, color: Colors.white, size: 30),
              onPressed: () => Navigator.pop(context),
            ),
          )
        ],
      ),
    );
  }
}
