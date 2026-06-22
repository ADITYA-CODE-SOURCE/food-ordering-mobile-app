import 'package:flutter/material.dart';

import 'models/cart_item.dart';
import 'models/food_item.dart';
import 'screens/login_screen.dart';
import 'screens/splash_screen.dart';
import 'utils/app_theme.dart';

void main() {
  runApp(const FoodOrderingApp());
}

class FoodOrderingApp extends StatefulWidget {
  const FoodOrderingApp({super.key});

  @override
  State<FoodOrderingApp> createState() => _FoodOrderingAppState();
}

class _FoodOrderingAppState extends State<FoodOrderingApp> {
  final List<CartItem> _cartItems = [];

  void addToCart(FoodItem foodItem, int quantity) {
    final index = _cartItems.indexWhere((item) => item.food.id == foodItem.id);

    setState(() {
      if (index >= 0) {
        _cartItems[index].quantity += quantity;
      } else {
        _cartItems.add(CartItem(food: foodItem, quantity: quantity));
      }
    });
  }

  void updateCartQuantity(int foodId, int quantity) {
    final index = _cartItems.indexWhere((item) => item.food.id == foodId);
    if (index == -1) return;

    setState(() {
      if (quantity <= 0) {
        _cartItems.removeAt(index);
      } else {
        _cartItems[index].quantity = quantity;
      }
    });
  }

  void removeFromCart(int foodId) {
    setState(() {
      _cartItems.removeWhere((item) => item.food.id == foodId);
    });
  }

  void clearCart() {
    setState(() {
      _cartItems.clear();
    });
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Food Ordering Mobile App',
      theme: AppTheme.lightTheme,
      home: SplashScreen(
        builder: () => LoginScreen(
          cartItems: _cartItems,
          onAddToCart: addToCart,
          onUpdateCart: updateCartQuantity,
          onRemoveFromCart: removeFromCart,
          onClearCart: clearCart,
        ),
      ),
    );
  }
}
