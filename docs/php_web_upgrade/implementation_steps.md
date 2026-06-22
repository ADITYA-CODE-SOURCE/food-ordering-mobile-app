# Step-by-Step Implementation Guide

## 1. Import the upgraded schema

```powershell
Get-Content "C:\Users\1111a\d\food-ordering-mobile-app\database\food_ordering_startup.sql" | & "C:\Program Files\MySQL\MySQL Server 9.2\bin\mysql.exe" -u root
```

If you already imported an older version, re-import this updated schema so the cart add-on column and latest fixes are applied.

## 2. Serve the PHP web application

```powershell
cd "C:\Users\1111a\d\food-ordering-mobile-app\php_app"
& "C:\Users\1111a\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" -S 127.0.0.1:9000
```

## 3. Open the customer application

- Visit `http://127.0.0.1:9000/menu.php`
- Create an admin account directly in MySQL before testing admin flows.
- Register a fresh customer account from `register.php` before testing customer flows.

## 4. Demo flow for interview

1. Show dynamic menu with category filter and sorting
2. Open a food details page and explain variants, add-ons, and reviews
3. Add an item to cart and go through checkout with coupon support
4. Place order and show order history timeline
5. Login as admin and show dashboard analytics
6. Edit food pricing and status from food management
7. Update an order status from Pending to Out For Delivery or Delivered

## 5. Files to explain in interview

- `database/food_ordering_startup.sql`
- `php_app/menu.php`
- `php_app/food.php`
- `php_app/actions/place_order_action.php`
- `php_app/admin/index.php`
- `php_app/admin/food_save.php`
- `php_app/includes/bootstrap.php`
- `php_app/includes/functions.php`
