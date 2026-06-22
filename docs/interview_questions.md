# Common Interview Questions and Answers

## 1. What is the main goal of this project?

The main goal is to build a full-stack food ordering system where users can browse food items, add them to cart, place orders, and store order details in a MySQL database using PHP APIs.

## 2. Why did you use Flutter?

I used Flutter because it helps build a clean mobile UI from a single codebase. It also provides useful widgets like `Scaffold`, `ListView`, `GridView`, `Card`, and `FutureBuilder`.

## 3. Why did you use PHP and MySQL for the backend?

PHP and MySQL are simple, popular, and beginner-friendly for CRUD-based projects. They are also commonly used in internship-level full-stack projects.

## 4. How does login work in your project?

The Flutter app sends email and password to the login API. In PHP, the backend fetches the user by email and checks the password using `password_verify()`. If valid, it returns user details in JSON.

## 5. How did you prevent SQL injection?

I used prepared statements in all important APIs such as login, registration, add to cart, and place order.

## 6. How is the cart managed?

The app keeps a local cart list for simple UI updates and also calls backend APIs to store cart data in the database.

## 7. How does order placement work?

When the user confirms the order, Flutter sends user id, address, total amount, and ordered items to the PHP API. The backend creates the order and order item rows inside a database transaction.

## 8. Why did you use `FutureBuilder`?

I used `FutureBuilder` to display API-based content like categories, food items, and orders while handling loading and error states cleanly.

## 9. What Flutter state management did you use?

I used `setState` because the project is beginner-friendly and small enough to manage without introducing advanced state management libraries.

## 10. What improvements can be added later?

- Store login session using shared preferences
- Add payment gateway integration
- Add search and favorites
- Add admin panel for managing food items
- Upload images from backend instead of using URLs
- Add order tracking statuses like Confirmed, Preparing, and Delivered

## 11. What database tables are used?

The project uses `users`, `categories`, `food_items`, `cart`, `orders`, and `order_items`.

## 12. How would you explain this project in one minute?

This is a food ordering mobile app where users can register, login, browse food categories, view food details, add items to cart, place orders, and see order history. The frontend is built in Flutter, and the backend is built with PHP APIs connected to MySQL. The project demonstrates authentication, API handling, JSON parsing, database design, and order management in a beginner-friendly way.
