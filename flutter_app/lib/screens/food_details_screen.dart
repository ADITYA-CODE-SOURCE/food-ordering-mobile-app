import 'package:flutter/material.dart';

import '../models/food_item.dart';
import '../services/cart_service.dart';
import '../services/food_service.dart';
import '../utils/app_constants.dart';
import '../utils/app_theme.dart';
import '../widgets/app_network_image.dart';
import '../widgets/async_button.dart';
import '../widgets/error_view.dart';
import '../widgets/loading_indicator.dart';
import '../widgets/quantity_selector.dart';

class FoodDetailsScreen extends StatefulWidget {
  const FoodDetailsScreen({
    super.key,
    required this.foodId,
    required this.onAddToCart,
  });

  final int foodId;
  final void Function(FoodItem foodItem, int quantity) onAddToCart;

  @override
  State<FoodDetailsScreen> createState() => _FoodDetailsScreenState();
}

class _FoodDetailsScreenState extends State<FoodDetailsScreen> {
  final FoodService _foodService = FoodService();
  final CartService _cartService = CartService();

  late Future<FoodItem> _foodFuture;
  int _quantity = 1;
  bool _isAdding = false;

  @override
  void initState() {
    super.initState();
    _foodFuture = _foodService.getFoodDetail(widget.foodId);
  }

  Future<void> _addToCart(FoodItem foodItem) async {
    setState(() {
      _isAdding = true;
    });

    try {
      final message = await _cartService.addToCart(foodId: foodItem.id, quantity: _quantity);
      widget.onAddToCart(foodItem, _quantity);

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
    } catch (error) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(error.toString().replaceFirst('Exception: ', ''))),
      );
    } finally {
      if (mounted) {
        setState(() {
          _isAdding = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Scaffold(
      appBar: AppBar(title: const Text('Food Details')),
      body: FutureBuilder<FoodItem>(
        future: _foodFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const LoadingIndicator();
          }

          if (snapshot.hasError) {
            return ErrorView(
              message: 'Unable to load food details.',
              onRetry: () {
                setState(() {
                  _foodFuture = _foodService.getFoodDetail(widget.foodId);
                });
              },
            );
          }

          final foodItem = snapshot.data!;
          final bannerHeight = MediaQuery.of(context).size.width >= AppConstants.tabletBreakpoint ? 320.0 : 260.0;

          return SingleChildScrollView(
            child: Center(
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: AppConstants.maxContentWidth),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Hero(
                      tag: 'food-image-${foodItem.id}',
                      child: AppNetworkImage(
                        imageUrl: foodItem.image,
                        width: double.infinity,
                        height: bannerHeight,
                        iconSize: 70,
                      ),
                    ),
                    Padding(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(foodItem.name, style: textTheme.headlineMedium),
                          const SizedBox(height: 8),
                          Wrap(
                            spacing: 12,
                            runSpacing: 12,
                            crossAxisAlignment: WrapCrossAlignment.center,
                            children: [
                              Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(Icons.star, color: Colors.amber),
                                  const SizedBox(width: 6),
                                  Text(foodItem.rating.toStringAsFixed(1)),
                                ],
                              ),
                              Text(
                                'Rs. ${foodItem.price.toStringAsFixed(0)}',
                                style: textTheme.titleLarge?.copyWith(color: AppTheme.accent),
                              ),
                              Chip(
                                label: Text(foodItem.isAvailable ? 'Available now' : 'Currently unavailable'),
                              ),
                            ],
                          ),
                          const SizedBox(height: 16),
                          Text(
                            foodItem.description,
                            style: textTheme.bodyLarge?.copyWith(color: AppTheme.mutedText),
                          ),
                          const SizedBox(height: 24),
                          Text('Quantity', style: textTheme.titleMedium),
                          const SizedBox(height: 8),
                          QuantitySelector(
                            quantity: _quantity,
                            onDecrease: () {
                              if (_quantity > 1) {
                                setState(() {
                                  _quantity--;
                                });
                              }
                            },
                            onIncrease: () {
                              setState(() {
                                _quantity++;
                              });
                            },
                          ),
                          const SizedBox(height: 24),
                          AsyncButton(
                            label: foodItem.isAvailable ? 'Add to Cart' : 'Not Available',
                            isLoading: _isAdding,
                            enabled: foodItem.isAvailable,
                            icon: Icons.shopping_cart_checkout,
                            onPressed: () => _addToCart(foodItem),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}
