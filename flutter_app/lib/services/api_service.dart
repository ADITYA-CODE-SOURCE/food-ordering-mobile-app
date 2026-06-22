import 'dart:convert';
import 'dart:async';

import 'package:http/http.dart' as http;

import '../utils/app_constants.dart';
import '../utils/session_manager.dart';

class ApiService {
  Future<Map<String, dynamic>> getRequest(String endpoint) async {
    try {
      final response = await http
          .get(
            Uri.parse('${AppConstants.baseUrl}/$endpoint'),
            headers: _headers(),
          )
          .timeout(AppConstants.apiTimeout);
      return _handleResponse(response);
    } on TimeoutException {
      throw Exception('The server took too long to respond. Please try again.');
    }
  }

  Future<Map<String, dynamic>> postRequest(
    String endpoint,
    Map<String, dynamic> body,
  ) async {
    try {
        final response = await http
          .post(
            Uri.parse('${AppConstants.baseUrl}/$endpoint'),
            headers: _headers(jsonBody: true),
            body: jsonEncode(body),
          )
          .timeout(AppConstants.apiTimeout);

      return _handleResponse(response);
    } on TimeoutException {
      throw Exception('The server took too long to respond. Please try again.');
    }
  }

  Map<String, dynamic> _handleResponse(http.Response response) {
    try {
      final decoded = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode >= 200 && response.statusCode < 300) {
        return decoded;
      }

      throw Exception(_extractMessage(decoded));
    } on FormatException {
      throw Exception('Server returned an invalid response.');
    }
  }

  String _extractMessage(Map<String, dynamic> decoded) {
    final message = decoded['message']?.toString().trim();
    if (message != null && message.isNotEmpty) {
      return message;
    }

    return 'Something went wrong.';
  }

  Map<String, String> _headers({bool jsonBody = false}) {
    final headers = <String, String>{
      'Accept': 'application/json',
    };

    if (jsonBody) {
      headers['Content-Type'] = 'application/json';
    }

    final token = SessionManager.authToken;
    if (token != null && token.isNotEmpty) {
      headers['Authorization'] = 'Bearer $token';
    }

    return headers;
  }
}
