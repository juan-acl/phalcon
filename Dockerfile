FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist \
    --optimize-autoloader --no-scripts --ignore-platform-reqs

FROM phalconphp/cphalcon:v5.22.1-php8.4
USER root
WORKDIR /app

COPY --chown=phalcon:phalcon . .
COPY --from=vendor --chown=phalcon:phalcon /app/vendor ./vendor

RUN mkdir -p var/cache/volt var/log public/uploads \
 && chown -R phalcon:phalcon var public/uploads \
 && php -r "require 'vendor/autoload.php'; exit(class_exists('Dompdf\Dompdf') ? 0 : 1);" \
 && php -m | grep -qi '^dom$' && php -m | grep -qi '^mbstring$'

USER phalcon
EXPOSE 10000
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-10000} -t public public/index.php"]
