FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    unzip \
    zip \
    nodejs npm

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
    curl \
    mbstring \
    zip \
    @composer

RUN mkdir -p /var/log/nginx \
    /var/log/supervisor \
    /run/nginx \
    /var/lib/nginx/tmp/client_body \
    /var/lib/nginx/tmp/proxy \
    /var/lib/nginx/tmp/fastcgi \
    /var/lib/nginx/tmp/uwsgi \
    /var/lib/nginx/tmp/scgi && \
    chown -R nobody:nobody /var/lib/nginx /var/log/nginx /run/nginx


WORKDIR /var/www/convert

COPY . /var/www/convert

COPY nginx.conf /etc/nginx/nginx.conf
COPY supervisord.conf /etc/supervisord.conf


RUN mkdir -p /var/www/convert/files && \
   chown -R nobody:nobody /var/www/convert && \
    chmod -R 755 /var/www/convert && \
    chmod -R 777 /var/www/convert/files

RUN composer install --no-dev --optimize-autoloader

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]