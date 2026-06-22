import 'package:flutter/material.dart';

import '../models/cart_item.dart';
import '../models/food_item.dart';
import '../services/auth_service.dart';
import '../utils/app_constants.dart';
import '../utils/app_router.dart';
import '../utils/app_theme.dart';
import '../utils/validators.dart';
import '../widgets/async_button.dart';
import 'home_screen.dart';
import 'register_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({
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
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final AuthService _authService = AuthService();

  bool _isLoading = false;
  String? _errorMessage;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      await _authService.login(
        _emailController.text.trim(),
        _passwordController.text.trim(),
      );

      if (!mounted) return;

      Navigator.pushReplacement(
        context,
        AppRouter.fadeSlide(
          HomeScreen(
            cartItems: widget.cartItems,
            onAddToCart: widget.onAddToCart,
            onUpdateCart: widget.onUpdateCart,
            onRemoveFromCart: widget.onRemoveFromCart,
            onClearCart: widget.onClearCart,
          ),
        ),
      );
    } catch (error) {
      setState(() {
        _errorMessage = error.toString().replaceFirst('Exception: ', '');
      });
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
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: AppConstants.maxFormWidth),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const SizedBox(height: 24),
                  Container(
                    width: 74,
                    height: 74,
                    decoration: BoxDecoration(
                      color: AppTheme.accent.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Icon(Icons.restaurant_menu, size: 34, color: AppTheme.accent),
                  ),
                  const SizedBox(height: 24),
                  Text('Welcome Back', style: textTheme.headlineLarge),
                  const SizedBox(height: 8),
                  Text(
                    'Login to explore food items, save favorites, and place your order faster.',
                    style: textTheme.bodyMedium?.copyWith(color: AppTheme.mutedText),
                  ),
                  const SizedBox(height: 32),
                  Form(
                    key: _formKey,
                    child: Column(
                      children: [
                        TextFormField(
                          controller: _emailController,
                          decoration: const InputDecoration(labelText: 'Email'),
                          validator: Validators.email,
                        ),
                        const SizedBox(height: 16),
                        TextFormField(
                          controller: _passwordController,
                          obscureText: true,
                          decoration: const InputDecoration(labelText: 'Password'),
                          validator: Validators.password,
                        ),
                        const SizedBox(height: 16),
                        AnimatedSwitcher(
                          duration: const Duration(milliseconds: 220),
                          child: _errorMessage == null
                              ? const SizedBox.shrink()
                              : Container(
                                  key: ValueKey(_errorMessage),
                                  width: double.infinity,
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    color: Colors.red.shade50,
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Text(
                                    _errorMessage!,
                                    style: const TextStyle(color: Colors.redAccent),
                                  ),
                                ),
                        ),
                        if (_errorMessage != null) const SizedBox(height: 16),
                        AsyncButton(
                          label: 'Login',
                          onPressed: _login,
                          isLoading: _isLoading,
                          icon: Icons.login,
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 24),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Text("Don't have an account?"),
                      TextButton(
                        onPressed: () {
                          Navigator.push(
                            context,
                            AppRouter.fadeSlide(const RegisterScreen()),
                          );
                        },
                        child: const Text('Register'),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
