import 'package:flutter/material.dart';

class AppColors {
  // Brand Primaries
  static const Color primary = Color(0xFF4F46E5);
  static const Color primaryLight = Color(0xFF818CF8);
  static const Color primaryDark = Color(0xFF3730A3);

  // Secondary & Accent
  static const Color secondary = Color(0xFF0D9488);
  static const Color accent = Color(0xFF6366F1);

  // Brand Gradients
  static const LinearGradient primaryGradient = LinearGradient(
    colors: [Color(0xFF4F46E5), Color(0xFF7C3AED)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  // Gemini AI Purple Accents
  static const Color aiPurple = Color(0xFF8B5CF6);
  static const Color aiPurpleLight = Color(0xFFEDE9FE);
  static const Color aiPurpleDark = Color(0xFF6D28D9);

  // Status & Performance Colors
  static const Color success = Color(0xFF10B981);
  static const Color successLight = Color(0xFFDCFCE7);
  static const Color successDark = Color(0xFF15803D);

  static const Color warning = Color(0xFFF59E0B);
  static const Color warningLight = Color(0xFFFEF3C7);
  static const Color warningDark = Color(0xFFB45309);

  static const Color error = Color(0xFFEF4444);
  static const Color errorLight = Color(0xFFFEE2E2);

  static const Color info = Color(0xFF0EA5E9);
  static const Color infoLight = Color(0xFFE0F2FE);

  // Platform Colors
  static const Color linkedin = Color(0xFF0A66C2);
  static const Color naukri = Color(0xFF1D4ED8);
  static const Color indeed = Color(0xFF4338CA);

  // Rank Medals
  static const Color gold = Color(0xFFFACC15);
  static const Color silver = Color(0xFFCBD5E1);
  static const Color bronze = Color(0xFFFB923C);

  // Backgrounds & Neutrals
  static const Color background = Color(0xFFF8FAFC);
  static const Color surface = Colors.white;
  static const Color surfaceVariant = Color(0xFFF1F5F9);
  static const Color border = Color(0xFFE2E8F0);
  static const Color divider = Color(0xFFEEF2F6);

  // Typography Colors
  static const Color textPrimary = Color(0xFF1E1B4B);
  static const Color textSecondary = Color(0xFF64748B);
  static const Color textLight = Color(0xFF94A3B8);
  static const Color textMuted = Color(0xFF94A3B8);
}
