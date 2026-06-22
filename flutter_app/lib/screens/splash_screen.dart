import 'dart:async';

import 'package:flutter/material.dart';

import '../utils/app_router.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key, required this.builder});

  final Widget Function() builder;

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _timer = Timer(const Duration(seconds: 2), () {
      if (!mounted) return;
      Navigator.pushReplacement(
        context,
        AppRouter.fadeSlide(widget.builder()),
      );
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFFFFE7D4), Color(0xFFFFF8F2)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        child: TweenAnimationBuilder<double>(
          tween: Tween(begin: 0.92, end: 1),
          duration: const Duration(milliseconds: 700),
          curve: Curves.easeOutBack,
          builder: (context, value, child) {
            return SafeArea(
              child: Center(
                child: Opacity(
                  opacity: value,
                  child: Transform.scale(scale: value, child: child),
                ),
              ),
            );
          },
          child: const Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              CircleAvatar(
                radius: 44,
                backgroundColor: Color(0xFFE86A33),
                child: Icon(Icons.restaurant_menu, color: Colors.white, size: 42),
              ),
              SizedBox(height: 18),
              Text(
                'Food Ordering Mobile App',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
              ),
              SizedBox(height: 8),
              Text('Fresh food, simple ordering'),
            ],
          ),
        ),
      ),
    );
  }
}
