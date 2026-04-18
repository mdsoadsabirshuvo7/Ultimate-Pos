import 'dart:io';

void main() {
  final file = File('C:/Users/User/Desktop/New folder (3)/Ultimate_POS_Flutter/lib/pages/medicine_ai_scanner.dart');
  var content = file.readAsStringSync();
  final start = content.indexOf('  void _extractMedicineInfo(String text) async {');
  final end = content.indexOf('  @override', start);
  
  if (start != -1 && end != -1) {
    final newFunc = '''
  void _extractMedicineInfo(String text) async {
    if (text.trim().isEmpty) return;

    setState(() {
      _detectedText = text.replaceAll('\\n', ' ').trim();
      if (_detectedText.length > 50) {
        _detectedText = _detectedText.substring(0, 50) + '...';
      }
    });

    final dosageRegex = RegExp(r'\\\\b(\\\\d+(?:\\\\.\\\\d+)?\\\\s*(mg|ml|g|mcg))\\\\b', caseSensitive: false);
    final match = dosageRegex.firstMatch(text);

    String name = 'Unknown';
    String dosage = '';
    final lines = text.split('\\n');

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

''';
    content = content.replaceRange(start, end, newFunc);
    file.writeAsStringSync(content);
    print('done');
  }
}
