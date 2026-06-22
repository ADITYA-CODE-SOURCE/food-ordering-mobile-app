# Food Ordering Mobile App

A beginner-friendly full-stack food ordering project built with Flutter, PHP, and MySQL.

## Project Modules

- `flutter_app/` - Flutter mobile frontend
- `php_backend/` - PHP REST APIs
- `php_app/` - PHP + MySQL responsive web application with admin dashboard
- `database/` - MySQL schema and sample data
- `docs/` - interview notes, flow, and structure

## Main Features

- Splash screen
- User registration and login
- Browse categories and food items
- View food details with quantity selector
- Add items to cart
- Checkout with address form
- Order success screen
- My orders screen
- Dynamic PHP web menu page with search, sorting, and categories
- Admin dashboard with analytics and CRUD screens
- Coupon system, wishlist, reviews, recently viewed, and order timeline
- PHP APIs with JSON responses
- MySQL database with sample food data

## Recommended Local Setup

1. Import `database/food_ordering_app.sql` into MySQL.
2. Copy `php_backend/` into your XAMPP or WAMP `htdocs` folder.
3. Update database credentials in `php_backend/config.php`.
4. Install Flutter dependencies inside `flutter_app/` using `flutter pub get`.
5. Update the API URL in `flutter_app/lib/utils/app_constants.dart`.
6. Run the PHP server and then start the Flutter app.

## PHP Web Upgrade Setup

1. Import `database/food_ordering_startup.sql` into MySQL.
2. Open `php_app/` using PHP built-in server or XAMPP.
3. Visit `menu.php` for the customer app and `admin/index.php` for the admin dashboard.
4. Use the demo accounts documented in `docs/php_web_upgrade/implementation_steps.md`.

## Notes

- The code is kept simple so you can explain it easily in an internship interview.
- Comments are only added where the logic may be slightly non-obvious.
- This workspace does not currently have `flutter` or `php` installed, so the project files were created but not executed here.

## Deployment

Deployment files are included:

- `Dockerfile` hosts the PHP web app, PHP API, and Flutter web build together.
- `render.yaml` defines a Render Docker web service.
- `docs/deployment.md` has the full deployment checklist.

Hosted paths:

- `/` - PHP web app and admin dashboard
- `/mobile` - Flutter web app
- `/api` - Flutter mobile REST API
