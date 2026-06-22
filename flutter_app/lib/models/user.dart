class User {
  final int id;
  final String name;
  final String email;
  final String phone;
  final String? apiToken;

  User({
    required this.id,
    required this.name,
    required this.email,
    required this.phone,
    this.apiToken,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: int.parse(json['id'].toString()),
      name: json['name'] ?? '',
      email: json['email'] ?? '',
      phone: json['phone'] ?? '',
      apiToken: json['api_token']?.toString(),
    );
  }
}
