import 'package:flutter/material.dart';

import '../models/cart_item.dart';
import '../services/order_service.dart';
import '../utils/app_constants.dart';
import '../utils/app_router.dart';
import '../utils/validators.dart';
import '../widgets/async_button.dart';
import 'order_success_screen.dart';

class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({
    super.key,
    required this.cartItems,
    required this.onClearCart,
  });

  final List<CartItem> cartItems;
  final VoidCallback onClearCart;

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  final _formKey = GlobalKey<FormState>();
  final _addressController = TextEditingController();
  final OrderService _orderService = OrderService();
  bool _isLoading = false;

  double get totalAmount =>
      widget.cartItems.fold<double>(0, (sum, item) => sum + item.totalPrice);

  @override
  void dispose() {
    _addressController.dispose();
    super.dispose();
  }

  Future<void> _placeOrder() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
    });

    try {
      final message = await _orderService.placeOrder(
        address: _addressController.text.trim(),
        items: widget.cartItems,
      );

      widget.onClearCart();

      if (!mounted) return;
      Navigator.pushAndRemoveUntil(
        context,
        AppRouter.fadeSlide(OrderSuccessScreen(message: message)),
        (route) => route.isFirst,
      );
    } catch (error) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(error.toString().replaceFirst('Exception: ', ''))),
      );
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Scaffold(
      appBar: AppBar(title: const Text('Checkout')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: AppConstants.maxFormWidth + 120),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Order Summary', style: textTheme.headlineMedium),
                const SizedBox(height: 12),
                ...widget.cartItems.map(
                  (item) => Card(
                    margin: const EdgeInsets.only(bottom: 10),
                    child: ListTile(
                      title: Text(item.food.name),
                      subtitle: Text('Quantity: ${item.quantity}'),
                      trailing: Text('Rs. ${item.totalPrice.toStringAsFixed(0)}'),
                    ),
                  ),
                ),
                const SizedBox(height: 14),
                Text(
                  'Total: Rs. ${totalAmount.toStringAsFixed(0)}',
                  style: textTheme.titleLarge,
                ),
                const SizedBox(height: 20),
                Form(
                  key: _formKey,
                  child: TextFormField(
                    controller: _addressController,
                    maxLines: 3,
                    decoration: const InputDecoration(
                      labelText: 'Delivery Address',
                      alignLabelWithHint: true,
                    ),
                    validator: (value) => Validators.requiredField(value, 'Address'),
                  ),
                ),
                const SizedBox(height: 18),
                AsyncButton(
                  label: 'Confirm Order',
                  onPressed: _placeOrder,
                  isLoading: _isLoading,
                  enabled: widget.cartItems.isNotEmpty,
                  icon: Icons.check_circle_outline,
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
