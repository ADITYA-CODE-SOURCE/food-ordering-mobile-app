# Recommended REST API Endpoints

These endpoints are the next-step API layer to align the upgraded PHP web project with the Flutter app.

## Auth

- `POST /api/auth/register`
- `POST /api/auth/login`
- `POST /api/auth/logout`
- `GET /api/auth/profile`
- `PUT /api/auth/profile`

## Catalog

- `GET /api/categories`
- `GET /api/banners`
- `GET /api/foods`
- `GET /api/foods/{slug}`
- `GET /api/foods/recommended`
- `GET /api/foods/trending`
- `GET /api/foods/recently-viewed`

## Cart & Wishlist

- `GET /api/cart`
- `POST /api/cart`
- `PATCH /api/cart/{id}`
- `DELETE /api/cart/{id}`
- `GET /api/favorites`
- `POST /api/favorites`
- `DELETE /api/favorites/{foodId}`

## Orders

- `POST /api/orders/checkout`
- `GET /api/orders`
- `GET /api/orders/{orderNumber}`
- `POST /api/orders/{orderNumber}/cancel`
- `POST /api/orders/{orderNumber}/reorder`

## Reviews

- `GET /api/foods/{id}/reviews`
- `POST /api/foods/{id}/reviews`

## Coupons

- `POST /api/coupons/validate`

## Admin

- `GET /api/admin/dashboard`
- `GET /api/admin/foods`
- `POST /api/admin/foods`
- `PUT /api/admin/foods/{id}`
- `DELETE /api/admin/foods/{id}`
- `GET /api/admin/orders`
- `PATCH /api/admin/orders/{id}/status`
- `GET /api/admin/users`
- `GET /api/admin/reports/sales`
