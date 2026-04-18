import 'package:flutter/material.dart';

class StyleColors{
  mainColor(double opacity){
    return Color(0xFF6C63FF).withValues(alpha: opacity);
  }

  secondColor(double opacity){
    return Colors.red.withValues(alpha: opacity);
  }

  accentColor(double opacity){
    return Colors.white.withValues(alpha: opacity);
  }
}