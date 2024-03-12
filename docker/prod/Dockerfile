FROM php:8.3.3-fpm

RUN apt-get update \
    && curl -sL https://deb.nodesource.com/setup_18.x -o nodesource_setup.sh \
    && bash nodesource_setup.sh \
    && apt install -y \
    gettext libzip-dev libxml2-dev libpng-dev \
    nginx nano cron git unzip supervisor \
    certbot python3-certbot-nginx \
    nodejs logrotate \
    && rm -r /var/lib/apt/lists/*

RUN pecl install --force redis \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis

RUN docker-php-ext-install \
    zip calendar dom gd \
    intl pcntl bcmath

ENV APP_HOME /var/www/

WORKDIR $APP_HOME

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

COPY ./supervisord.conf /etc/supervisord.conf
COPY ./nginx.conf /etc/nginx/nginx.conf
COPY ./www.conf /usr/local/etc/php-fpm.d/www.conf
COPY ./php.ini /usr/local/etc/php/php.ini
COPY ./laravel.logrotate /etc/logrotate.d/laravel

#change ownership of our applications
RUN chown -R www-data:www-data $APP_HOME

COPY ./docker-entrypoint.sh /
RUN chmod +x /docker-entrypoint.sh

CMD ["/docker-entrypoint.sh"]
