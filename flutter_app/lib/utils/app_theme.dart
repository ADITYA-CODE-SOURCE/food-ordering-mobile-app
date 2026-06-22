import 'package:flutter/material.dart';

class AppTheme {
  static const Color accent = Color(0xFFE86A33);
  static const Color accentDark = Color(0xFFCB5827);
  static const Color background = Color(0xFFFFF8F2);
  static const Color surface = Colors.white;
  static const Color textPrimary = Color(0xFF2D1B12);
  static const Color mutedText = Color(0xFF6E625B);
  static const Color warmPlaceholder = Color(0xFFF6E7DA);
  static const Color successTint = Color(0xFFDFF6DD);
  static const double radiusMd = 14;
  static const double radiusLg = 18;

  static ThemeData get lightTheme {
    final scheme = ColorScheme.fromSeed(
      seedColor: accent,
      primary: accent,
      secondary: const Color(0xFFFFB26B),
      surface: surface,
    );

    return ThemeData(
      useMaterial3: true,
      colorScheme: scheme,
      scaffoldBackgroundColor: background,
      textTheme: const TextTheme(
        headlineLarge: TextStyle(fontSize: 30, fontWeight: FontWeight.w800, color: textPrimary),
        headlineMedium: TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: textPrimary),
        titleLarge: TextStyle(fontSize: 20, fontWeight: FontWeight.w700, color: textPrimary),
        titleMedium: TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: textPrimary),
        bodyLarge: TextStyle(fontSize: 16, color: textPrimary, height: 1.4),
        bodyMedium: TextStyle(fontSize: 14, color: textPrimary, height: 1.45),
      ),
      appBarTheme: const AppBarTheme(
        centerTitle: true,
        backgroundColor: Colors.transparent,
        foregroundColor: textPrimary,
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: surface,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
        labelStyle: const TextStyle(color: mutedText),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(radiusMd),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(radiusMd),
          borderSide: BorderSide.none,
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(radiusMd),
          borderSide: const BorderSide(color: accent, width: 1.2),
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          minimumSize: const Size(double.infinity, 52),
          backgroundColor: accent,
          foregroundColor: Colors.white,
          textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(radiusMd),
          ),
        ),
      ),
      cardTheme: CardThemeData(
        color: surface,
        elevation: 1,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(radiusLg),
        ),
      ),
      chipTheme: ChipThemeData(
        backgroundColor: warmPlaceholder,
        selectedColor: accent,
        secondarySelectedColor: accent,
        labelStyle: const TextStyle(color: textPrimary, fontWeight: FontWeight.w600),
        secondaryLabelStyle: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(999)),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      ),
    );
  }
}
