import 'food_item.dart';

class CartItem {
  final FoodItem food;
  int quantity;

  CartItem({required this.food, required this.quantity});

  double get totalPrice => food.price * quantity;
}
