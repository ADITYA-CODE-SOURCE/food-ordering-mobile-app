FROM ghcr.io/cirruslabs/flutter:stable AS flutter_build

WORKDIR /app
COPY flutter_app/pubspec.* ./
RUN flutter pub get
COPY flutter_app/ ./
RUN flutter build web --release --dart-define=API_BASE_URL=/api

FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

COPY php_app/ /var/www/html/
COPY php_backend/ /var/www/html/api/
COPY database/food_ordering_startup.sql /var/www/html/database/food_ordering_startup.sql
COPY --from=flutter_build /app/build/web/ /var/www/html/mobile/
COPY deploy/apache-start.sh /usr/local/bin/apache-start
COPY deploy/init-db.php /usr/local/bin/init-db.php

RUN chmod +x /usr/local/bin/apache-start \
    && chown -R www-data:www-data /var/www/html/uploads

EXPOSE 80

CMD ["apache-start"]
