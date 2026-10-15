FROM phalconphp/cphalcon:v5.22.1-php8.4
WORKDIR /app
COPY . .
RUN mkdir -p var/cache var/log public/uploads
EXPOSE 10000
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-10000} -t public public/index.php"]
