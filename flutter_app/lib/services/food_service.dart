import '../models/category.dart';
import '../models/food_item.dart';
import 'api_service.dart';

class FoodService {
  final ApiService _apiService = ApiService();

  Future<List<Category>> getCategories() async {
    final response = await _apiService.getRequest('get_categories.php');
    final data = response['data'] as List<dynamic>;
    return data.map((item) => Category.fromJson(item)).toList();
  }

  Future<List<FoodItem>> getFoodItems({int? categoryId}) async {
    String endpoint = 'get_food_items.php';
    if (categoryId != null) {
      endpoint = '$endpoint?category_id=$categoryId';
    }

    final response = await _apiService.getRequest(endpoint);
    final data = response['data'] as List<dynamic>;
    return data.map((item) => FoodItem.fromJson(item)).toList();
  }

  Future<FoodItem> getFoodDetail(int foodId) async {
    final response = await _apiService.getRequest('get_food_detail.php?food_id=$foodId');
    return FoodItem.fromJson(response['data']);
  }
}
