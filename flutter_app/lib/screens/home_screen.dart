import 'package:flutter/material.dart';

import '../models/cart_item.dart';
import '../models/category.dart';
import '../models/food_item.dart';
import '../services/auth_service.dart';
import '../services/food_service.dart';
import '../utils/app_constants.dart';
import '../utils/app_router.dart';
import '../utils/session_manager.dart';
import '../utils/app_theme.dart';
import '../widgets/category_chip.dart';
import '../widgets/error_view.dart';
import '../widgets/food_card.dart';
import '../widgets/loading_indicator.dart';
import '../widgets/section_header.dart';
import 'cart_screen.dart';
import 'food_details_screen.dart';
import 'login_screen.dart';
import 'my_orders_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({
    super.key,
    required this.cartItems,
    required this.onAddToCart,
    required this.onUpdateCart,
    required this.onRemoveFromCart,
    required this.onClearCart,
  });

  final List<CartItem> cartItems;
  final void Function(FoodItem foodItem, int quantity) onAddToCart;
  final void Function(int foodId, int quantity) onUpdateCart;
  final void Function(int foodId) onRemoveFromCart;
  final VoidCallback onClearCart;

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final FoodService _foodService = FoodService();
  final AuthService _authService = AuthService();
  final Set<int> _favoriteIds = <int>{};
  final TextEditingController _searchController = TextEditingController();

  late Future<List<Category>> _categoriesFuture;
  late Future<List<FoodItem>> _foodItemsFuture;
  int? _selectedCategoryId;
  String _searchQuery = '';
  bool _topRatedOnly = false;

  @override
  void initState() {
    super.initState();
    _categoriesFuture = _foodService.getCategories();
    _foodItemsFuture = _foodService.getFoodItems();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _loadFoodItems() {
    setState(() {
      _foodItemsFuture = _foodService.getFoodItems(categoryId: _selectedCategoryId);
    });
  }

  List<FoodItem> _applyFilters(List<FoodItem> items) {
    var filtered = items.where((item) {
      final matchesSearch = _searchQuery.isEmpty ||
          item.name.toLowerCase().contains(_searchQuery) ||
          item.description.toLowerCase().contains(_searchQuery);
      final matchesRating = !_topRatedOnly || item.rating >= 4.5;
      return matchesSearch && matchesRating;
    }).toList();

    filtered.sort((a, b) {
      final favoriteComparison = (_favoriteIds.contains(b.id) ? 1 : 0) - (_favoriteIds.contains(a.id) ? 1 : 0);
      if (favoriteComparison != 0) {
        return favoriteComparison;
      }

      return b.rating.compareTo(a.rating);
    });

    return filtered;
  }

  void _toggleFavorite(int foodId) {
    setState(() {
      if (_favoriteIds.contains(foodId)) {
        _favoriteIds.remove(foodId);
      } else {
        _favoriteIds.add(foodId);
      }
    });
  }

  int get _cartCount => widget.cartItems.fold<int>(0, (sum, item) => sum + item.quantity);

  @override
  Widget build(BuildContext context) {
    final user = SessionManager.currentUser;
    final textTheme = Theme.of(context).textTheme;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Food Menu'),
        actions: [
          IconButton(
            onPressed: () async {
              await Navigator.push(
                context,
                AppRouter.fadeSlide(const MyOrdersScreen()),
              );
              setState(() {});
            },
            icon: const Icon(Icons.receipt_long),
          ),
          Stack(
            children: [
              IconButton(
                onPressed: () async {
                  await Navigator.push(
                    context,
                    AppRouter.fadeSlide(
                      CartScreen(
                        cartItems: widget.cartItems,
                        onUpdateCart: widget.onUpdateCart,
                        onRemoveFromCart: widget.onRemoveFromCart,
                        onClearCart: widget.onClearCart,
                      ),
                    ),
                  );
                  setState(() {});
                },
                icon: const Icon(Icons.shopping_cart_outlined),
              ),
              Positioned(
                right: 8,
                top: 8,
                child: AnimatedSwitcher(
                  duration: const Duration(milliseconds: 220),
                  child: _cartCount > 0
                      ? CircleAvatar(
                          key: ValueKey(_cartCount),
                          radius: 9,
                          backgroundColor: Colors.redAccent,
                          child: Text(
                            _cartCount.toString(),
                            style: const TextStyle(fontSize: 11, color: Colors.white),
                          ),
                        )
                      : const SizedBox.shrink(),
                ),
              ),
            ],
          ),
        ],
      ),
      drawer: Drawer(
        child: ListView(
          padding: EdgeInsets.zero,
          children: [
            UserAccountsDrawerHeader(
              currentAccountPicture: const CircleAvatar(
                child: Icon(Icons.person),
              ),
              accountName: Text(user?.name ?? 'Guest User'),
              accountEmail: Text(user?.email ?? 'No email'),
            ),
            ListTile(
              leading: const Icon(Icons.receipt_long),
              title: const Text('My Orders'),
              onTap: () {
                Navigator.pop(context);
                Navigator.push(
                  context,
                  AppRouter.fadeSlide(const MyOrdersScreen()),
                );
              },
            ),
            ListTile(
              leading: const Icon(Icons.logout),
              title: const Text('Logout'),
              onTap: () {
                _authService.logout();
                widget.onClearCart();
                Navigator.pushAndRemoveUntil(
                  context,
                  AppRouter.fadeSlide(
                    LoginScreen(
                      cartItems: widget.cartItems,
                      onAddToCart: widget.onAddToCart,
                      onUpdateCart: widget.onUpdateCart,
                      onRemoveFromCart: widget.onRemoveFromCart,
                      onClearCart: widget.onClearCart,
                    ),
                  ),
                  (route) => false,
                );
              },
            ),
          ],
        ),
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          _categoriesFuture = _foodService.getCategories();
          _foodItemsFuture = _foodService.getFoodItems(categoryId: _selectedCategoryId);
          setState(() {});
        },
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            Center(
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: AppConstants.maxContentWidth),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [Color(0xFFFFE7D4), Colors.white],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.circular(24),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const SectionHeader(
                            title: 'Discover tasty food',
                            subtitle: 'Fresh meals, quick ordering, and a smoother experience on every screen.',
                          ),
                          const SizedBox(height: 18),
                          TextField(
                            controller: _searchController,
                            onChanged: (value) {
                              setState(() {
                                _searchQuery = value.trim().toLowerCase();
                              });
                            },
                            decoration: InputDecoration(
                              hintText: 'Search pizza, burger, brownie...',
                              prefixIcon: const Icon(Icons.search),
                              suffixIcon: _searchQuery.isEmpty
                                  ? null
                                  : IconButton(
                                      onPressed: () {
                                        _searchController.clear();
                                        setState(() {
                                          _searchQuery = '';
                                        });
                                      },
                                      icon: const Icon(Icons.close),
                                    ),
                            ),
                          ),
                          const SizedBox(height: 14),
                          Wrap(
                            spacing: 10,
                            runSpacing: 10,
                            children: [
                              FilterChip(
                                selected: _topRatedOnly,
                                onSelected: (value) {
                                  setState(() {
                                    _topRatedOnly = value;
                                  });
                                },
                                label: const Text('Top rated 4.5+'),
                              ),
                              Chip(
                                avatar: const Icon(Icons.favorite, size: 16, color: Colors.redAccent),
                                label: Text('${_favoriteIds.length} favorites'),
                              ),
                              Chip(
                                avatar: const Icon(Icons.shopping_bag_outlined, size: 16),
                                label: Text('$_cartCount items in cart'),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),
                    FutureBuilder<List<Category>>(
                      future: _categoriesFuture,
                      builder: (context, snapshot) {
                        if (snapshot.connectionState == ConnectionState.waiting) {
                          return const SizedBox(height: 70, child: LoadingIndicator());
                        }

                        if (snapshot.hasError) {
                          return ErrorView(
                            message: 'Unable to load categories.',
                            onRetry: () {
                              setState(() {
                                _categoriesFuture = _foodService.getCategories();
                              });
                            },
                          );
                        }

                        final categories = snapshot.data ?? [];

                        return SizedBox(
                          height: 48,
                          child: ListView(
                            scrollDirection: Axis.horizontal,
                            children: [
                              ChoiceChip(
                                label: const Text('All'),
                                selected: _selectedCategoryId == null,
                                onSelected: (_) {
                                  _selectedCategoryId = null;
                                  _loadFoodItems();
                                },
                              ),
                              const SizedBox(width: 10),
                              ...categories.map(
                                (category) => CategoryChip(
                                  category: category,
                                  isSelected: _selectedCategoryId == category.id,
                                  onTap: () {
                                    _selectedCategoryId = category.id;
                                    _loadFoodItems();
                                  },
                                ),
                              ),
                            ],
                          ),
                        );
                      },
                    ),
                    const SizedBox(height: 18),
                    FutureBuilder<List<FoodItem>>(
                      future: _foodItemsFuture,
                      builder: (context, snapshot) {
                        if (snapshot.connectionState == ConnectionState.waiting) {
                          return const SizedBox(height: 320, child: LoadingIndicator(message: 'Loading menu...'));
                        }

                        if (snapshot.hasError) {
                          return ErrorView(
                            message: 'Unable to load food items.',
                            onRetry: _loadFoodItems,
                          );
                        }

                        final filteredItems = _applyFilters(snapshot.data ?? []);

                        if (filteredItems.isEmpty) {
                          return const Padding(
                            padding: EdgeInsets.symmetric(vertical: 32),
                            child: Center(child: Text('No food items match your current filters.')),
                          );
                        }

                        return LayoutBuilder(
                          builder: (context, constraints) {
                            final width = constraints.maxWidth;
                            final crossAxisCount = width >= AppConstants.desktopBreakpoint
                                ? 4
                                : width >= AppConstants.tabletBreakpoint
                                    ? 3
                                    : 2;

                            return Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Text('Popular picks', style: textTheme.titleLarge),
                                    const Spacer(),
                                    Text(
                                      '${filteredItems.length} items',
                                      style: textTheme.bodyMedium?.copyWith(color: AppTheme.mutedText),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 12),
                                GridView.builder(
                                  shrinkWrap: true,
                                  physics: const NeverScrollableScrollPhysics(),
                                  itemCount: filteredItems.length,
                                  gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                                    crossAxisCount: crossAxisCount,
                                    childAspectRatio: width >= AppConstants.tabletBreakpoint ? 0.84 : 0.72,
                                    crossAxisSpacing: 12,
                                    mainAxisSpacing: 12,
                                  ),
                                  itemBuilder: (context, index) {
                                    final item = filteredItems[index];

                                    return FoodCard(
                                      foodItem: item,
                                      isFavorite: _favoriteIds.contains(item.id),
                                      onFavoriteToggle: () => _toggleFavorite(item.id),
                                      onTap: () async {
                                        await Navigator.push(
                                          context,
                                          AppRouter.fadeSlide(
                                            FoodDetailsScreen(
                                              foodId: item.id,
                                              onAddToCart: widget.onAddToCart,
                                            ),
                                          ),
                                        );
                                        setState(() {});
                                      },
                                    );
                                  },
                                ),
                              ],
                            );
                          },
                        );
                      },
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
