import 'package:flutter/material.dart';

class AppTheme {
  static const Color orange = Color(0xFFFF6B00);
  static const Color orangeLight = Color(0xFFFF8C3A);
  static const Color dark = Color(0xFF06091B); // deep background
  static const Color darkCard = Color(0x0CFFFFFF); // semi-translucent glass card
  static const Color darkBg = Color(0xFF0D1128); // secondary background
  static const Color line = Color(0x14FFFFFF); // subtle borders
  static const Color text = Color(0xFFF0F2FF);
  static const Color muted = Color(0x8CFFFFFF);
  static const Color textDim = Color(0x73FFFFFF);
  static const Color green = Color(0xFF4ADE80);
  static const Color red = Color(0xFFF87171);
  static const Color yellow = Color(0xFFF59E0B);

  static ThemeData get darkTheme {
    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.dark,
      primaryColor: orange,
      scaffoldBackgroundColor: darkBg,
      colorScheme: const ColorScheme.dark(
        primary: orange,
        secondary: orangeLight,
        background: darkBg,
        surface: dark,
        error: red,
      ),
      textTheme: const TextTheme(
        headlineLarge: TextStyle(fontSize: 32, fontWeight: FontWeight.w800, color: text, letterSpacing: -0.8),
        headlineMedium: TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: text, letterSpacing: -0.5),
        titleLarge: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: text),
        bodyLarge: TextStyle(fontSize: 16, color: text),
        bodyMedium: TextStyle(fontSize: 14, color: muted),
        labelLarge: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: textDim, letterSpacing: 0.5),
      ),
      cardTheme: CardThemeData(
        color: dark,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: line, width: 1),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: darkCard,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        hintStyle: const TextStyle(color: textDim, fontSize: 14),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: line),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: line),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: orange, width: 1.5),
        ),
      ),
    );
  }
}
