## Container file for Maarch Courrier dependency only

# TODO switch to 8.2/8.3
ARG BASE_REGISTRY=docker.io/

FROM ${BASE_REGISTRY}php:8.1-apache-bullseye as base

# Copy dependencies lists
COPY ./container/dependences.apt ./container/dependences.php /mnt/

## Dependencies
# Binaries dependencies + configuration
RUN apt-get update \
    && apt-get install --no-install-recommends -y debsecan \
    && apt-get install --no-install-recommends -y $(debsecan --suite buster --format packages --only-fixed) \
    && apt-get purge -y debsecan \
    && apt-get install --no-install-recommends -y $(cat /mnt/dependences.apt) \
    && sed -i 's/rights="none" pattern="PDF"/rights="read" pattern="PDF"/' /etc/ImageMagick-6/policy.xml \
    && rm -rf /var/lib/apt/lists/* \
    && rm -rf /mnt/dependences.apt \
# Install PHP extension installer with dependency manager
    && curl -sSLf \
               -o /usr/local/bin/install-php-extensions \
               https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions \
    && chmod +x /usr/local/bin/install-php-extensions  \
# Install PHP extensions
    && install-php-extensions $(cat /mnt/dependences.php) \
    && rm -rf /usr/local/bin/install-php-extensions \
    && rm -rf /mnt/dependences.php \
# Set locales
    && echo "fr_FR.UTF-8 UTF-8" > /etc/locale.gen \
    && locale-gen \
    && echo "LC_ALL=fr_FR.UTF-8" > /etc/environment \
# Apache mods
    && a2enmod rewrite headers \
    && sed -i 's/^ServerTokens.*/ServerTokens Prod/' /etc/apache2/conf-available/security.conf \
    && sed -i 's/^ServerSignature.*/ServerSignature Off/' /etc/apache2/conf-available/security.conf

## Application files
# Create the MaarchCourrier dirs
RUN mkdir -p --mode=700 /var/www/html/MaarchCourrier /opt/maarch/docservers \
  && chown www-data:www-data /var/www/html/MaarchCourrier /opt/maarch/docservers

WORKDIR /var/www/html/MaarchCourrier

## Openssl config
COPY --chmod=644 container/openssl.cnf /etc/ssl/openssl.cnf

# Apache vhost
COPY container/default-vhost.conf /etc/apache2/sites-available/000-default.conf

# PHP Configuration
COPY container/php.ini /usr/local/etc/php/php.ini

# Set default healthcheck
COPY --chown=root:root --chmod=500 container/healthcheck.sh /bin/healthcheck.sh

# run cron in the background
RUN sed -i 's/^exec /service cron start\n\nexec /' /usr/local/bin/apache2-foreground
