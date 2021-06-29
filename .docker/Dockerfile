FROM php:8.0.3-cli-alpine

ARG TIMEZONE=${TIMEZONE:-Europe/Paris}
ARG DEV_ENV=${DEV_ENV:-dev}

RUN apk add bash
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN docker-php-ext-install iconv;\
    docker-php-ext-enable iconv
#RUN docker-php-ext-install pdo_mysql
#RUN docker-php-ext-enable pdo_mysql
RUN apk add g++ autoconf make re2c && \
    pecl install mailparse && \
    docker-php-ext-enable mailparse && \
    apk del g++ autoconf make re2c

ENV COMPOSER_ALLOW_SUPERUSER 1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
&& composer --version

RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone \
&& printf '[PHP]\ndate.timezone = "%s"\n', ${TIMEZONE} > /usr/local/etc/php/conf.d/tzone.ini \
&& "date"

RUN apk add --update make inotify-tools nodejs yarn;\
    yarn global add nodemon --prefix /usr/local

COPY entrypoint.sh /entrypoint.sh
ENTRYPOINT /entrypoint.sh
RUN ["chmod", "+x", "/entrypoint.sh"]
