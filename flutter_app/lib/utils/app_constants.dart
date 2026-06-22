import 'package:flutter/foundation.dart';

class AppConstants {
  static const String _configuredApiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: '',
  );

  static const String appName = 'Food Ordering Mobile App';
  static const Duration apiTimeout = Duration(seconds: 15);
  static const double maxContentWidth = 1100;
  static const double maxFormWidth = 460;
  static const double tabletBreakpoint = 700;
  static const double desktopBreakpoint = 1024;

  static String get baseUrl {
    if (_configuredApiBaseUrl.isNotEmpty) {
      return _configuredApiBaseUrl;
    }

    if (kIsWeb || defaultTargetPlatform == TargetPlatform.windows) {
      return 'http://127.0.0.1:8000';
    }

    return 'http://10.0.2.2/food_ordering_mobile_app/php_backend';
  }
}
