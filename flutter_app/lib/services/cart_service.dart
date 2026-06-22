import '../utils/session_manager.dart';
import 'api_service.dart';

class CartService {
  final ApiService _apiService = ApiService();

  Future<String> addToCart({required int foodId, required int quantity}) async {
    final user = SessionManager.currentUser;
    if (user == null) {
      throw Exception('Please login first.');
    }

    final response = await _apiService.postRequest('add_to_cart.php', {
      'user_id': user.id,
      'food_id': foodId,
      'quantity': quantity,
    });

    return response['message'] ?? 'Item added to cart.';
  }

  Future<String> removeFromCart(int foodId) async {
    final user = SessionManager.currentUser;
    if (user == null) {
      throw Exception('Please login first.');
    }

    final response = await _apiService.postRequest('remove_from_cart.php', {
      'user_id': user.id,
      'food_id': foodId,
    });

    return response['message'] ?? 'Item removed from cart.';
  }
}
