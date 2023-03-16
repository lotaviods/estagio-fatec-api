FROM php:8.1-apache

RUN apt-get update && apt-get install -y \
		libfreetype6-dev \
		libjpeg62-turbo-dev \
		libpng-dev \
        git \
        curl \
        zip \
        unzip \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_mysql


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

#Web Service conf
COPY ./docker/virtualhost.conf /etc/apache2/sites-available/fateclink.com.conf

RUN a2enmod rewrite
RUN a2ensite fateclink.com.conf
RUN a2dissite 000-default.conf