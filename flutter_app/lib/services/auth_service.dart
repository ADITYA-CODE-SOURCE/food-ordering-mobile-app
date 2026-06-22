import '../models/user.dart';
import '../utils/session_manager.dart';
import 'api_service.dart';

class AuthService {
  final ApiService _apiService = ApiService();

  Future<User> login(String email, String password) async {
    final response = await _apiService.postRequest('login.php', {
      'email': email,
      'password': password,
    });

    final user = User.fromJson({
      ...(response['data']['user'] as Map<String, dynamic>),
      'api_token': response['data']['token'],
    });
    SessionManager.currentUser = user;
    return user;
  }

  Future<String> register({
    required String name,
    required String email,
    required String phone,
    required String password,
  }) async {
    final response = await _apiService.postRequest('register.php', {
      'name': name,
      'email': email,
      'phone': phone,
      'password': password,
    });

    return response['message'] ?? 'Registration successful.';
  }

  void logout() {
    SessionManager.currentUser = null;
  }
}
