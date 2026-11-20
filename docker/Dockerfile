# =============================================================
# PROYECTO TRI FUERZA - Servidor Laravel
# PHP 8.4 + Apache + Node.js 24 + Composer 2 (PostgreSQL)
# =============================================================
FROM php:8.4-apache-bookworm

ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_HOME=/var/www/.composer

# UID/GID de tu usuario de Ubuntu (llegan desde .env -> compose.yaml)
ARG HOST_UID=1000
ARG HOST_GID=1000

# -------------------------------------------------------------
# Paquetes del sistema + extensiones de PHP
# (mbstring, xml, ctype, tokenizer, etc. ya vienen en la imagen)
# -------------------------------------------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
        ca-certificates curl git gnupg nano unzip \
        libfreetype6-dev libicu-dev libjpeg62-turbo-dev \
        libpng-dev libpq-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath exif gd intl opcache pcntl pdo_pgsql pgsql zip \
    && rm -rf /var/lib/apt/lists/*

# -------------------------------------------------------------
# Node.js 24 LTS (repositorio firmado de NodeSource)
# -------------------------------------------------------------
RUN mkdir -p /etc/apt/keyrings \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key \
       | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_24.x nodistro main" \
       > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

# -------------------------------------------------------------
# Composer 2 (desde la imagen oficial)
# -------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# -------------------------------------------------------------
# www-data = tu usuario de Ubuntu: lo que creen composer,
# artisan y npm queda a tu nombre, no de root.
# -------------------------------------------------------------
RUN groupmod -o -g "${HOST_GID}" www-data \
    && usermod -o -u "${HOST_UID}" -g "${HOST_GID}" www-data

# -------------------------------------------------------------
# Apache: http://<carpeta>.localhost:8083 -> www/<carpeta>/public
# -------------------------------------------------------------
RUN a2enmod rewrite headers expires vhost_alias

COPY docker/apache/sites.conf /etc/apache2/sites-available/laravel.conf
COPY docker/inicio /var/www/inicio
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

RUN echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && a2dissite 000-default \
    && a2ensite laravel \
    && chown -R www-data:www-data /var/www

WORKDIR /var/www/html

EXPOSE 80 5173

HEALTHCHECK --interval=30s --timeout=5s --retries=3 \
    CMD curl -fsS -o /dev/null http://localhost/ || exit 1

CMD ["apache2-foreground"]
