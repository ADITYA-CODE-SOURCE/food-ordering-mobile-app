class OrderModel {
  final int id;
  final double totalAmount;
  final String address;
  final String orderStatus;
  final String createdAt;
  final List<OrderItemModel> items;

  OrderModel({
    required this.id,
    required this.totalAmount,
    required this.address,
    required this.orderStatus,
    required this.createdAt,
    required this.items,
  });

  factory OrderModel.fromJson(Map<String, dynamic> json) {
    final itemsJson = (json['items'] as List<dynamic>? ?? []);

    return OrderModel(
      id: int.parse(json['id'].toString()),
      totalAmount: double.parse(json['total_amount'].toString()),
      address: json['address'] ?? '',
      orderStatus: json['order_status'] ?? '',
      createdAt: json['created_at'] ?? '',
      items: itemsJson.map((item) => OrderItemModel.fromJson(item)).toList(),
    );
  }
}

class OrderItemModel {
  final int foodId;
  final String foodName;
  final int quantity;
  final double price;

  OrderItemModel({
    required this.foodId,
    required this.foodName,
    required this.quantity,
    required this.price,
  });

  factory OrderItemModel.fromJson(Map<String, dynamic> json) {
    return OrderItemModel(
      foodId: int.parse(json['food_id'].toString()),
      foodName: json['food_name'] ?? '',
      quantity: int.parse(json['quantity'].toString()),
      price: double.parse(json['price'].toString()),
    );
  }
}
