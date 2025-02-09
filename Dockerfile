FROM php:8.1-fpm

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    nginx \
    nodejs \
    npm \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Copia a configuração do Nginx
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Define o diretório de trabalho
WORKDIR /var/www/html

# Expondo a porta 80
EXPOSE 80

# Inicia o Nginx e o PHP-FPM
CMD service nginx start && php-fpm
