import '../models/user.dart';

class SessionManager {
  static User? currentUser;

  static String? get authToken => currentUser?.apiToken;
}
