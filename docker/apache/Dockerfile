ARG PHP_VERSION=7.3

FROM php:${PHP_VERSION}-apache as rpps-base

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update --fix-missing
RUN apt-get install -y curl nano unzip
RUN apt-get install -y build-essential libssl-dev zlib1g-dev libpng-dev libjpeg-dev libfreetype6-dev libzip-dev

RUN apt-get update \
	&& apt-get install -y \
		libfreetype6-dev \
		libpng-dev \
		libjpeg-dev \
	&& docker-php-ext-configure gd \
		--with-freetype-dir=/usr/include/ \
		--with-jpeg-dir=/usr/include/ \
		--with-png-dir=/usr/include/ \
	&& docker-php-ext-install -j$(nproc) \
		gd \
	&& apt-get purge -y \
		libfreetype6-dev \
		libpng-dev \
		libjpeg-dev

RUN apt-get update \
	&& apt-get install -y \
		libicu-dev \
	&& docker-php-ext-install -j$(nproc) \
		intl \
	&& apt-get purge -y \
		libicu-dev

RUN apt-get update \
	&& apt-get install -y \
		libxml2-dev \
	&& docker-php-ext-install -j$(nproc) \
		xml \
	&& apt-get purge -y \
		libxml2-dev

RUN apt install -y libcurl3-dev
RUN docker-php-ext-install pdo_mysql zip mbstring opcache curl json

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions http

RUN a2enmod rewrite ssl headers

RUN php -r "copy('https://getcomposer.org/installer', '/tmp/composer-setup.php');" && php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer && rm -rf /tmp/composer-setup.php
RUN php /usr/local/bin/composer config --global --auth github-oauth.github.com 72cf2aa7d18c411d9a6776cf5bd388b805aac68d

RUN rm -vf /var/lib/apt/lists/* && apt-get update && apt-get install -y git

WORKDIR /var/www/html

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions http dom iconv mbstring xml intl

FROM rpps-base AS builder

COPY --chown=www-data:www-data . /var/www/html/
COPY ./docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY  ./docker/apache/custom.ini /usr/local/etc/php/conf.d/custom.ini

RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 -subj "/C=FR/ST=Ile-De-France/L=Paris/O=Instamed/OU=IT Department/CN=localhost" -keyout /etc/ssl/private/apache-selfsigned.key -out /etc/ssl/certs/apache-selfsigned.crt

RUN composer install
RUN php bin/console assets:install --symlink

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
