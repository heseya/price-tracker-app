FROM php:8.2-apache
WORKDIR /usr/src/app
ENV APACHE_DOCUMENT_ROOT /usr/src/app/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite

RUN docker-php-ext-install pdo_mysql

RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    wget

RUN wget https://github.com/maglnet/ComposerRequireChecker/releases/latest/download/composer-require-checker.phar \
    -O /usr/bin/composer-require-checker
RUN wget https://github.com/vimeo/psalm/releases/latest/download/psalm.phar \
    -O /usr/bin/psalm
RUN wget https://github.com/composer-unused/composer-unused/releases/latest/download/composer-unused.phar \
    -O /usr/bin/composer-unused
RUN wget http://github.com/ergebnis/composer-normalize/releases/latest/download/composer-normalize.phar \
    -O /usr/bin/composer-normalize

RUN chmod 777 /usr/bin/composer-require-checker \
    /usr/bin/psalm \
    /usr/bin/composer-unused \
    /usr/bin/composer-normalize

COPY --from=composer /usr/bin/composer /usr/bin/composer
