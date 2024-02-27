

FROM local/courrier-dependencies:main as base_app

# Copy the app files inside the container
# ordered from least likely to change, to most likely (to optimize build cache)
COPY --chown=www-data:www-data index.php LICENSE.txt CONTRIBUTING.md CLA.md .htaccess /var/www/html/MaarchCourrier/
COPY --chown=www-data:www-data modules /var/www/html/MaarchCourrier/modules
COPY --chown=www-data:www-data install /var/www/html/MaarchCourrier/install
COPY --chown=www-data:www-data rest /var/www/html/MaarchCourrier/rest
COPY --chown=www-data:www-data bin /var/www/html/MaarchCourrier/bin
COPY --chown=www-data:www-data config /var/www/html/MaarchCourrier/config
COPY --chown=www-data:www-data referential /var/www/html/MaarchCourrier/referential
COPY --chown=www-data:www-data sql /var/www/html/MaarchCourrier/sql
COPY --chown=www-data:www-data migration /var/www/html/MaarchCourrier/migration
COPY --chown=www-data:www-data package.json package-lock.json composer.json composer.lock /var/www/html/MaarchCourrier/
COPY --chown=www-data:www-data src /var/www/html/MaarchCourrier/src

# Correct permissions
RUN find /var/www/html/MaarchCourrier -type d -exec chmod 770 {} + \
    & find /var/www/html/MaarchCourrier -type f -exec chmod 660 {} + \
    & chmod 770 /opt/maarch/docservers \
    & chmod 440 /usr/local/etc/php/php.ini \
    & wait


#
# PHP build vendor
#
FROM composer:lts AS composer

# Get composer depencies list + app PHP files
COPY composer.json composer.lock /app/

COPY src/app /app/src/app
COPY src/core /app/src/core
COPY src/backend /app/src/backend

RUN composer install --ignore-platform-reqs --no-scripts --no-dev \
    && composer dump-autoload --classmap-authoritative

#
# Front build
# TODO addin
#
FROM node:20.9-alpine AS front

WORKDIR /app

COPY package.json package-lock.json angular.json tsconfig.base.json /app/

RUN  npm -v && node -v \
  && npm ci --legacy-peer-deps

COPY src/frontend /app/src/frontend/

RUN npm run build-prod \
    && mv node_modules/tinymce tinymce/ \
    && mv node_modules/tinymce-i18n tinymce-i18n/ \
    && rm -rf node_modules


FROM base_app as app

# Copy built vendor + dist folders
COPY --chown=www-data:www-data --from=composer /app/vendor ./vendor/
COPY --chown=www-data:www-data --from=front /app/dist ./dist/
COPY --chown=www-data:www-data --from=front /app/tinymce ./node_modules/tinymce
COPY --chown=www-data:www-data --from=front /app/tinymce-i18n ./node_modules/tinymce-i18n

# Set default entrypoint
COPY --chown=root:www-data container/entrypoint.sh /bin/entrypoint.sh
ENTRYPOINT ["/bin/entrypoint.sh"]

# Correct permissions
RUN find /var/www/html/MaarchCourrier -type d -exec chmod 700 {} + \
    & find /var/www/html/MaarchCourrier -type f -exec chmod 600 {} + \
    & chmod 700 /opt/maarch/docservers \
    & chmod 444 /usr/local/etc/php/php.ini \
    & chmod 500 /bin/entrypoint.sh \
    & wait


CMD ["/usr/local/bin/apache2-foreground"]

FROM app as dev_api

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY container/php-dev.ini "${PHP_INI_DIR}"/conf.d/
