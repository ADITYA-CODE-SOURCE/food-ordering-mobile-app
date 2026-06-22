import 'package:flutter/material.dart';

import '../models/cart_item.dart';
import '../services/cart_service.dart';
import '../utils/app_constants.dart';
import '../utils/app_router.dart';
import '../utils/app_theme.dart';
import '../widgets/app_network_image.dart';
import '../widgets/async_button.dart';
import '../widgets/quantity_selector.dart';
import 'checkout_screen.dart';

class CartScreen extends StatefulWidget {
  const CartScreen({
    super.key,
    required this.cartItems,
    required this.onUpdateCart,
    required this.onRemoveFromCart,
    required this.onClearCart,
  });

  final List<CartItem> cartItems;
  final void Function(int foodId, int quantity) onUpdateCart;
  final void Function(int foodId) onRemoveFromCart;
  final VoidCallback onClearCart;

  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  final CartService _cartService = CartService();

  double get totalAmount =>
      widget.cartItems.fold<double>(0, (sum, item) => sum + item.totalPrice);

  Future<void> _removeItem(int foodId) async {
    try {
      await _cartService.removeFromCart(foodId);
    } catch (error) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(error.toString().replaceFirst('Exception: ', ''))),
      );
    }

    widget.onRemoveFromCart(foodId);
    setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Scaffold(
      appBar: AppBar(title: const Text('My Cart')),
      body: widget.cartItems.isEmpty
          ? const Center(child: Text('Your cart is empty.'))
          : Column(
              children: [
                Expanded(
                  child: ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: widget.cartItems.length,
                    itemBuilder: (context, index) {
                      final cartItem = widget.cartItems[index];

                      return Center(
                        child: ConstrainedBox(
                          constraints: const BoxConstraints(maxWidth: AppConstants.maxContentWidth),
                          child: Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            child: Padding(
                              padding: const EdgeInsets.all(14),
                              child: Row(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  AppNetworkImage(
                                    imageUrl: cartItem.food.image,
                                    width: 72,
                                    height: 72,
                                    borderRadius: BorderRadius.circular(12),
                                    iconSize: 28,
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(cartItem.food.name, style: textTheme.titleMedium?.copyWith(fontSize: 16)),
                                        const SizedBox(height: 6),
                                        Text(
                                          'Rs. ${cartItem.food.price.toStringAsFixed(0)} each',
                                          style: textTheme.bodyMedium?.copyWith(color: AppTheme.mutedText),
                                        ),
                                        const SizedBox(height: 8),
                                        QuantitySelector(
                                          quantity: cartItem.quantity,
                                          onDecrease: () {
                                            final newQuantity = cartItem.quantity - 1;
                                            widget.onUpdateCart(cartItem.food.id, newQuantity);
                                            setState(() {});
                                          },
                                          onIncrease: () {
                                            final newQuantity = cartItem.quantity + 1;
                                            widget.onUpdateCart(cartItem.food.id, newQuantity);
                                            setState(() {});
                                          },
                                        ),
                                      ],
                                    ),
                                  ),
                                  Column(
                                    crossAxisAlignment: CrossAxisAlignment.end,
                                    children: [
                                      Text(
                                        'Rs. ${cartItem.totalPrice.toStringAsFixed(0)}',
                                        style: textTheme.titleMedium?.copyWith(color: AppTheme.accent),
                                      ),
                                      IconButton(
                                        onPressed: () => _removeItem(cartItem.food.id),
                                        icon: const Icon(Icons.delete_outline, color: Colors.redAccent),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                      );
                    },
                  ),
                ),
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: const BoxDecoration(
                    color: Colors.white,
                    boxShadow: [
                      BoxShadow(color: Colors.black12, blurRadius: 6, offset: Offset(0, -2)),
                    ],
                  ),
                  child: Column(
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Total Amount',
                            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                          ),
                          Text(
                            'Rs. ${totalAmount.toStringAsFixed(0)}',
                            style: const TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFFE86A33),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 14),
                      AsyncButton(
                        label: 'Proceed to Checkout',
                        icon: Icons.arrow_forward,
                        onPressed: () {
                          Navigator.push(
                            context,
                            AppRouter.fadeSlide(
                              CheckoutScreen(
                                cartItems: widget.cartItems,
                                onClearCart: widget.onClearCart,
                              ),
                            ),
                          ).then((_) => setState(() {}));
                        },
                      ),
                    ],
                  ),
                ),
              ],
            ),
    );
  }
}
