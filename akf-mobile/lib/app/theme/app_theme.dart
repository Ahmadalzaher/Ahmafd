import 'package:flutter/material.dart';

class AppColors {
  static const brown = Color(0xFF402315);
  static const gold = Color(0xFFC99A45);
  static const cream = Color(0xFFF8F0DF);
  static const beige = Color(0xFFE9D3A6);
  static const white = Colors.white;
  static const green = Color(0xFF2E7D4F);
  static const red = Color(0xFFB3261E);
  static const orange = Color(0xFFE08A00);
  static const blue = Color(0xFF2A6FBB);
}

ThemeData buildTheme({required Locale locale}) {
  final base = ThemeData(
    useMaterial3: true,
    colorScheme: ColorScheme.fromSeed(
      seedColor: AppColors.brown,
      primary: AppColors.brown,
      secondary: AppColors.gold,
      surface: AppColors.cream,
      error: AppColors.red,
    ),
    scaffoldBackgroundColor: AppColors.cream,
    appBarTheme: const AppBarTheme(
      backgroundColor: AppColors.brown,
      foregroundColor: AppColors.white,
      centerTitle: true,
      elevation: 0,
    ),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: AppColors.brown,
        foregroundColor: AppColors.white,
        minimumSize: const Size.fromHeight(56),
        textStyle: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: AppColors.white,
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
    ),
    cardTheme: CardThemeData(
      color: AppColors.white,
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
    ),
  );
  return base.copyWith(
    textTheme: base.textTheme.apply(
      bodyColor: AppColors.brown,
      displayColor: AppColors.brown,
    ),
  );
}
