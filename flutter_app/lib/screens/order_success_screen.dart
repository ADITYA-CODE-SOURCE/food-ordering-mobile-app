import 'package:flutter/material.dart';

import '../utils/app_theme.dart';
import '../widgets/async_button.dart';

class OrderSuccessScreen extends StatelessWidget {
  const OrderSuccessScreen({super.key, required this.message});

  final String message;

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Scaffold(
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: TweenAnimationBuilder<double>(
            tween: Tween(begin: 0.9, end: 1),
            duration: const Duration(milliseconds: 420),
            curve: Curves.easeOutBack,
            builder: (context, value, child) {
              return Opacity(
                opacity: value.clamp(0.0, 1.0),
                child: Transform.scale(scale: value, child: child),
              );
            },
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const CircleAvatar(
                  radius: 38,
                  backgroundColor: AppTheme.successTint,
                  child: Icon(Icons.check, size: 44, color: Colors.green),
                ),
                const SizedBox(height: 18),
                Text(
                  'Order Placed Successfully!',
                  textAlign: TextAlign.center,
                  style: textTheme.headlineMedium,
                ),
                const SizedBox(height: 10),
                Text(message, textAlign: TextAlign.center, style: textTheme.bodyLarge),
                const SizedBox(height: 24),
                AsyncButton(
                  label: 'Back to Home',
                  icon: Icons.home_outlined,
                  onPressed: () {
                    Navigator.popUntil(context, (route) => route.isFirst);
                  },
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
