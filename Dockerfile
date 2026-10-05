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
# Estágio 2: imagem final, só com o necessário para executar
# ------------------------------------------------------------
FROM php:8.4-cli-alpine AS app

# A imagem oficial já traz o driver do SQLite. O do MySQL é instalado aqui.
RUN docker-php-ext-install pdo_mysql

WORKDIR /app

COPY --from=dependencias /app/vendor/ vendor/
COPY . .

# Pasta do banco SQLite (usada quando DB_DSN não é informado) com dono não-root.
RUN mkdir -p var/data && chown -R www-data:www-data var

# Nunca rode a aplicação como root dentro do container.
USER www-data

# Servidor embutido do PHP com 4 processos.
# Serve para estudo; na semana 6 ele será trocado por PHP-FPM + NGINX.
ENV PHP_CLI_SERVER_WORKERS=4
EXPOSE 8000

HEALTHCHECK --interval=10s --timeout=3s --start-period=5s --retries=3 \
    CMD wget -qO- http://127.0.0.1:8000/health || exit 1

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public", "public/index.php"]
