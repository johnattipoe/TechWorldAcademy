FROM node:22-bookworm-slim AS assets
WORKDIR /workspace
COPY package.json package-lock.json ./
RUN npm ci
COPY app ./app
COPY public ./public
COPY src ./src
COPY tsconfig.json ./
RUN npm run build

FROM php:8.3-apache-bookworm AS app
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN apt-get update \
    && apt-get install -y --no-install-recommends libcurl4-openssl-dev libonig-dev \
    && docker-php-ext-install curl mbstring \
    && rm -rf /var/lib/apt/lists/* \
    && (getent group 1000 >/dev/null || groupadd --gid 1000 render-secrets) \
    && usermod -a -G 1000 www-data \
    && sed -ri "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && a2enmod rewrite headers \
    && echo 'ServerName localhost' >> /etc/apache2/apache2.conf
COPY docker/apache-public.conf /etc/apache2/conf-available/techworld-public.conf
RUN a2enconf techworld-public
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
COPY . .
COPY --from=assets /workspace/public/assets/css/compiled/tailwind.css ./public/assets/css/compiled/tailwind.css
COPY --from=assets /workspace/public/assets/js/compiled/main.js ./public/assets/js/compiled/main.js
COPY docker/render-entrypoint.sh /usr/local/bin/render-entrypoint
RUN chmod 755 /usr/local/bin/render-entrypoint \
    && mkdir -p storage/framework/sessions storage/framework/rate-limits \
    && chown -R www-data:www-data storage/framework \
    && chmod 750 storage/framework storage/framework/sessions storage/framework/rate-limits
EXPOSE 10000
ENTRYPOINT ["/usr/local/bin/render-entrypoint"]
CMD ["apache2-foreground"]