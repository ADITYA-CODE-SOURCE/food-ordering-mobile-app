import 'package:flutter/material.dart';

import '../models/order_model.dart';
import '../services/order_service.dart';
import '../utils/app_constants.dart';
import '../utils/app_theme.dart';
import '../widgets/error_view.dart';
import '../widgets/loading_indicator.dart';

class MyOrdersScreen extends StatefulWidget {
  const MyOrdersScreen({super.key});

  @override
  State<MyOrdersScreen> createState() => _MyOrdersScreenState();
}

class _MyOrdersScreenState extends State<MyOrdersScreen> {
  final OrderService _orderService = OrderService();
  late Future<List<OrderModel>> _ordersFuture;

  @override
  void initState() {
    super.initState();
    _ordersFuture = _orderService.getOrders();
  }

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Scaffold(
      appBar: AppBar(title: const Text('My Orders')),
      body: FutureBuilder<List<OrderModel>>(
        future: _ordersFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const LoadingIndicator();
          }

          if (snapshot.hasError) {
            return ErrorView(
              message: 'Unable to fetch orders.',
              onRetry: () {
                setState(() {
                  _ordersFuture = _orderService.getOrders();
                });
              },
            );
          }

          final orders = snapshot.data ?? [];

          if (orders.isEmpty) {
            return const Center(child: Text('No orders found yet.'));
          }

          return RefreshIndicator(
            onRefresh: () async {
              setState(() {
                _ordersFuture = _orderService.getOrders();
              });
              await _ordersFuture;
            },
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: orders.length,
              itemBuilder: (context, index) {
                final order = orders[index];

                return Center(
                  child: ConstrainedBox(
                    constraints: const BoxConstraints(maxWidth: AppConstants.maxContentWidth),
                    child: Card(
                      margin: const EdgeInsets.only(bottom: 14),
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Text('Order #${order.id}', style: textTheme.titleMedium),
                                const Spacer(),
                                Chip(label: Text(order.orderStatus)),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Text('Date: ${order.createdAt}', style: textTheme.bodyMedium?.copyWith(color: AppTheme.mutedText)),
                            Text('Address: ${order.address}', style: textTheme.bodyMedium?.copyWith(color: AppTheme.mutedText)),
                            Text('Total: Rs. ${order.totalAmount.toStringAsFixed(0)}', style: textTheme.titleSmall),
                            const Divider(height: 22),
                            Text('Items', style: textTheme.titleSmall),
                            const SizedBox(height: 6),
                            ...order.items.map(
                              (item) => Padding(
                                padding: const EdgeInsets.only(bottom: 4),
                                child: Text(
                                  '${item.foodName} x ${item.quantity} - Rs. ${item.price.toStringAsFixed(0)}',
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                );
              },
            ),
          );
        },
      ),
    );
  }
}
