# syntax=docker/dockerfile:1

# ------------------------------------------------------------
# Estágio 1: instala as dependências PHP com o Composer
# ------------------------------------------------------------
FROM composer:2 AS dependencias

WORKDIR /app

# Copia primeiro só os arquivos de dependências: enquanto eles não mudarem,
# o Docker reaproveita esta camada do cache e o build fica muito mais rápido.
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-autoloader

COPY src/ src/
RUN composer dump-autoload --no-dev --optimize

# ------------------------------------------------------------
# Estágio 2: imagem final com PHP-FPM (atende o NGINX via FastCGI na porta 9000)
# ------------------------------------------------------------
FROM php:8.4-fpm-alpine AS app

# Driver do MySQL e OPcache (cache do código PHP compilado).
RUN docker-php-ext-install pdo_mysql opcache

COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache-agenda.ini
COPY docker/php-fpm-agenda.conf /usr/local/etc/php-fpm.d/zz-agenda.conf

WORKDIR /app

COPY --from=dependencias /app/vendor/ vendor/
COPY . .

# Pasta do banco SQLite (usada quando DB_DSN não é informado) com dono não-root.
RUN mkdir -p var/data && chown -R www-data:www-data var

# Nunca rode a aplicação como root dentro do container.
USER www-data

# FastCGI, não HTTP: só o NGINX conversa com esta porta.
EXPOSE 9000

# O PHP-FPM não fala HTTP: o healthcheck verifica se a porta FastCGI está aceitando conexões.
HEALTHCHECK --interval=10s --timeout=3s --start-period=5s --retries=3 \
    CMD php -r 'exit(@fsockopen("127.0.0.1", 9000) ? 0 : 1);'

CMD ["php-fpm"]
