# Шаг 1: Используем официальный образ PHP с установленным Apache
FROM php:8.4-apache

# Шаг 2: Устанавливаем системные пакеты и библиотеки
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    libpq-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Шаг 3: Конфигурируем и устанавливаем все расширения PHP
RUN docker-php-ext-configure intl \
    && docker-php-ext-install intl zip pdo pdo_mysql pdo_pgsql pgsql

# Шаг 4: Включаем модуль rewrite в Apache (критически важно для роутинга Symfony)
RUN a2enmod rewrite

# Шаг 5: Меняем корневую директорию Apache на public/ и разрешаем чтение переменных окружения
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf \
    && echo "PassEnv DATABASE_URL" >> /etc/apache2/apache2.conf \
    && echo "PassEnv APP_SECRET" >> /etc/apache2/apache2.conf \
    && echo "PassEnv APP_ENV" >> /etc/apache2/apache2.conf

# Шаг 6: Устанавливаем Composer внутрь контейнера
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Шаг 7: Копируем файлы проекта в контейнер
WORKDIR /var/www/html
COPY . .

# Шаг 8: Задаем дефолтные переменные для сборки и устанавливаем зависимости PHP
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APP_ENV=prod
ENV APP_SECRET=Def4ultSecr3tStrt1ngForProdDuclt10n
ENV DATABASE_URL=sqlite:///%kernel.project_dir%/var/data.db
RUN composer install --no-dev --optimize-autoloader --no-scripts

RUN php bin/console importmap:install

# Шаг 9: Создаем папку var, выставляем права владельца www-data и полные права на запись кэша
RUN mkdir -p /var/www/html/var \
    && chown -R www-data:www-data /var/www/html/var \
    && chmod -R 775 /var/www/html/var

# Шаг 10: Указываем порт и запускаем Apache с миграциями
EXPOSE 80
CMD php bin/console doctrine:migrations:migrate --no-interaction \
    && chown -R www-data:www-data /var/www/html/var \
    && chmod -R 775 /var/www/html/var \
    && apache2-foreground