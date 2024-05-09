FROM php:8.2

RUN apt update && apt install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev

RUN apt clean && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

COPY --from=composer:1.10.19 /usr/bin/composer /usr/bin/composer
COPY ./ /app
RUN useradd -G www-data,root -u 1000 -d /home/user1 user1

RUN mkdir -p /home/user1/.composer && \
    chown -R user1:user1 /home/user1 && \
    chmod -R 777 /app && \
    chmod -R 777 /home/user1

WORKDIR /app

RUN composer install

EXPOSE 80
EXPOSE 8000
