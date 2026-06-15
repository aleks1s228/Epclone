# Шаг 1: Используем официальный образ PHP с установленным Apache
FROM php:8.4-apache

# Шаг 2: Устанавливаем системные пакеты и необходимые для Symfony расширения (инструменты для ZIP, intl для латышского языка и PDO для БД)
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl pdo pdo_mysql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Шаг 3: Включаем модуль rewrite в Apache (критически важно для роутинга Symfony)
RUN a2enmod rewrite

# Шаг 4: Меняем корневую директорию Apache на public/ (как требует Symfony)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Шаг 5: Устанавливаем Composer внутрь контейнера
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Шаг 6: Копируем файлы проекта в контейнер
WORKDIR /var/www/html
COPY . .

# Шаг 7: Устанавливаем зависимости PHP без девелоперских пакетов и оптимизируем автозагрузчик
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APP_ENV=prod
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Шаг 8: Выставляем правильные права на папки кэша и логов, чтобы Symfony могла в них писать
RUN mkdir -p /var/www/html/var && chown -R www-data:www-data /var/www/html/var

# Шаг 9: Указываем порт, который слушает контейнер (Render сам пробросит его наружу)
EXPOSE 80

# Шаг 10: Запускаем Apache на переднем плане
CMD ["apache2-foreground"]