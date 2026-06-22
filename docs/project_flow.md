# Project Flow

## 1. User Registration

- New user opens the app.
- User goes to the registration screen.
- Flutter validates name, email, phone, and password.
- App sends data to `register.php`.
- PHP checks if email already exists.
- If valid, user data is saved in MySQL.

## 2. User Login

- User enters email and password.
- App sends credentials to `login.php`.
- PHP verifies the password using `password_verify()`.
- User data is returned in JSON format.
- Flutter stores the current user in a simple session class.

## 3. Browse Food Items

- Home screen loads categories using `get_categories.php`.
- Home screen loads food items using `get_food_items.php`.
- User can filter food items by category.

## 4. View Food Details

- User taps a food card.
- App opens the details screen.
- Food data is fetched from `get_food_detail.php`.
- User selects quantity and taps Add to Cart.

## 5. Cart Handling

- Flutter updates the local cart list.
- App also calls `add_to_cart.php` to keep backend cart data.
- User can remove items from cart using `remove_from_cart.php`.

## 6. Place Order

- User opens checkout screen.
- User enters address.
- Flutter validates the address field.
- App sends order data to `place_order.php`.
- PHP creates one row in `orders` and multiple rows in `order_items`.
- PHP clears the cart table for that user.

## 7. View Previous Orders

- User opens My Orders screen.
- App calls `get_orders.php`.
- PHP returns all user orders with nested order items.
- Flutter shows order history in cards.

## Why This Flow Is Good For Interviews

- It covers authentication, API integration, JSON parsing, database design, and CRUD operations.
- It uses simple Flutter state handling with `setState`, which is easy to explain.
- It shows full-stack understanding without advanced complexity.
