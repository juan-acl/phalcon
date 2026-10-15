FROM phalconphp/cphalcon:v5.22.1-php8.4
USER root
WORKDIR /app
COPY --chown=phalcon:phalcon . .
RUN mkdir -p var/cache/volt var/log public/uploads && chown -R phalcon:phalcon var public/uploads
USER phalcon
EXPOSE 10000
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-10000} -t public public/index.php"]
