import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

enum DateTimePickerType { date, time, dateTime }

class DateTimePicker extends StatefulWidget {
  const DateTimePicker({
    super.key,
    this.controller,
    this.type = DateTimePickerType.date,
    this.initialValue,
    this.firstDate,
    this.lastDate,
    this.dateLabelText,
    this.dateMask,
    this.style,
    this.textAlign = TextAlign.start,
    this.locale,
    this.use24HourFormat = false,
    this.onChanged,
    this.validator,
  });

  final TextEditingController? controller;
  final DateTimePickerType type;
  final String? initialValue;
  final DateTime? firstDate;
  final DateTime? lastDate;
  final String? dateLabelText;
  final String? dateMask;
  final TextStyle? style;
  final TextAlign textAlign;
  final Locale? locale;
  final bool use24HourFormat;
  final ValueChanged<String>? onChanged;
  final String? Function(String?)? validator;

  @override
  State<DateTimePicker> createState() => _DateTimePickerState();
}

class _DateTimePickerState extends State<DateTimePicker> {
  late final TextEditingController _controller;
  bool _ownsController = false;

  @override
  void initState() {
    super.initState();
    _controller = widget.controller ?? TextEditingController();
    _ownsController = widget.controller == null;

    if (_controller.text.isEmpty &&
        widget.initialValue != null &&
        widget.initialValue!.trim().isNotEmpty) {
      _controller.text = widget.initialValue!.trim();
    }
  }

  @override
  void dispose() {
    if (_ownsController) {
      _controller.dispose();
    }
    super.dispose();
  }

  DateTime _effectiveFirstDate() {
    return widget.firstDate ?? DateTime(2000, 1, 1);
  }

  DateTime _effectiveLastDate() {
    return widget.lastDate ?? DateTime(2100, 12, 31);
  }

  DateTime _clampToBounds(DateTime value) {
    final first = _effectiveFirstDate();
    final last = _effectiveLastDate();

    if (value.isBefore(first)) {
      return first;
    }
    if (value.isAfter(last)) {
      return last;
    }
    return value;
  }

  DateTime _parseOrNow(String? value) {
    if (value == null || value.trim().isEmpty) {
      return DateTime.now();
    }

    final trimmed = value.trim();
    DateTime? parsed = DateTime.tryParse(trimmed);
    parsed ??= DateTime.tryParse(trimmed.replaceFirst(' ', 'T'));

    if (parsed != null) {
      return parsed;
    }

    const formats = [
      'yyyy-MM-dd HH:mm:ss',
      'yyyy-MM-dd HH:mm',
      'yyyy-MM-dd hh:mm a',
      'yyyy-MM-dd',
      'HH:mm',
      'hh:mm a',
    ];

    for (final pattern in formats) {
      try {
        return DateFormat(pattern).parseStrict(trimmed);
      } catch (_) {
        // Try the next known date format.
      }
    }

    return DateTime.now();
  }

  String _format(DateTime value) {
    switch (widget.type) {
      case DateTimePickerType.date:
        return DateFormat('yyyy-MM-dd').format(value);
      case DateTimePickerType.time:
        return DateFormat(widget.use24HourFormat ? 'HH:mm' : 'hh:mm a')
            .format(value);
      case DateTimePickerType.dateTime:
        return DateFormat('yyyy-MM-dd HH:mm:ss').format(value);
    }
  }

  Future<void> _pick() async {
    final current = _clampToBounds(_parseOrNow(_controller.text));
    DateTime selected = current;

    if (widget.type == DateTimePickerType.date ||
        widget.type == DateTimePickerType.dateTime) {
      final date = await showDatePicker(
        context: context,
        initialDate: current,
        firstDate: _effectiveFirstDate(),
        lastDate: _effectiveLastDate(),
      );

      if (date == null) {
        return;
      }

      selected = DateTime(
        date.year,
        date.month,
        date.day,
        selected.hour,
        selected.minute,
        selected.second,
      );
    }

    if (widget.type == DateTimePickerType.time ||
        widget.type == DateTimePickerType.dateTime) {
      final time = await showTimePicker(
        context: context,
        initialTime: TimeOfDay.fromDateTime(selected),
        builder: (context, child) {
          if (child == null) {
            return const SizedBox.shrink();
          }
          return MediaQuery(
            data: MediaQuery.of(context).copyWith(
              alwaysUse24HourFormat: widget.use24HourFormat,
            ),
            child: child,
          );
        },
      );

      if (time == null) {
        return;
      }

      selected = DateTime(
        selected.year,
        selected.month,
        selected.day,
        time.hour,
        time.minute,
      );
    }

    final formatted = _format(selected);
    setState(() {
      _controller.text = formatted;
    });
    widget.onChanged?.call(formatted);
  }

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      controller: _controller,
      readOnly: true,
      textAlign: widget.textAlign,
      style: widget.style,
      onTap: _pick,
      validator: widget.validator,
      decoration: InputDecoration(
        labelText: widget.dateLabelText,
        suffixIcon: const Icon(Icons.calendar_today),
      ),
    );
  }
}