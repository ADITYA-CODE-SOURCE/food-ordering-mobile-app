class FoodItem {
  final int id;
  final int categoryId;
  final String name;
  final String description;
  final double price;
  final String image;
  final double rating;
  final bool isAvailable;

  FoodItem({
    required this.id,
    required this.categoryId,
    required this.name,
    required this.description,
    required this.price,
    required this.image,
    required this.rating,
    required this.isAvailable,
  });

  factory FoodItem.fromJson(Map<String, dynamic> json) {
    return FoodItem(
      id: int.parse(json['id'].toString()),
      categoryId: int.parse(json['category_id'].toString()),
      name: json['name'] ?? '',
      description: json['description'] ?? '',
      price: double.parse(json['price'].toString()),
      image: json['image'] ?? '',
      rating: double.parse(json['rating'].toString()),
      isAvailable: json['is_available'].toString() == '1',
    );
  }
}
