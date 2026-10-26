import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'core/theme/app_theme.dart';
import 'screens/splash_screen.dart';

/// ---------------------------------------------------------------------------
/// THE FLUTTER ENTRY POINT: main()
/// 
/// 1. `main()` is the very first function executed when your mobile app launches.
/// 2. `runApp()` tells Flutter: "Take this widget and draw it onto the device screen".
/// 3. `ProviderScope` is Riverpod's master store. It holds all app state (User login,
///    job postings, AI candidate rankings, inquiries) so any screen can access them!
/// ---------------------------------------------------------------------------

import 'core/services/storage_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await StorageService.init();

  runApp(
    const ProviderScope(
      child: AiLoopingApp(),
    ),
  );
}

/// The Root Widget of the Mobile Application
class AiLoopingApp extends StatelessWidget {
  const AiLoopingApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'AI Looping',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      // The app starts at SplashScreen to check session status
      home: const SplashScreen(),
    );
  }
}
