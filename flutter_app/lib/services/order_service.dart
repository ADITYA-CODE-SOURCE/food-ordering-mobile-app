import '../models/cart_item.dart';
import '../models/order_model.dart';
import '../utils/session_manager.dart';
import 'api_service.dart';

class OrderService {
  final ApiService _apiService = ApiService();

  Future<String> placeOrder({
    required String address,
    required List<CartItem> items,
  }) async {
    final user = SessionManager.currentUser;
    if (user == null) {
      throw Exception('Please login first.');
    }

    final totalAmount = items.fold<double>(0, (sum, item) => sum + item.totalPrice);

    final response = await _apiService.postRequest('place_order.php', {
      'user_id': user.id,
      'address': address,
      'total_amount': totalAmount,
      'items': items
          .map(
            (item) => {
              'food_id': item.food.id,
              'quantity': item.quantity,
              'price': item.food.price,
            },
          )
          .toList(),
    });

    return response['message'] ?? 'Order placed successfully.';
  }

  Future<List<OrderModel>> getOrders() async {
    final user = SessionManager.currentUser;
    if (user == null) {
      throw Exception('Please login first.');
    }

    final response = await _apiService.postRequest('get_orders.php', {
      'user_id': user.id,
    });

    final data = response['data'] as List<dynamic>;
    return data.map((item) => OrderModel.fromJson(item)).toList();
  }
}
