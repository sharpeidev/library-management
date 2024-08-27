FROM php:8.3.8-fpm

RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl

RUN docker-php-ext-install pdo pdo_mysql gd exif pcntl bcmath opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /var/www/html

WORKDIR /var/www/html

# RUN chmod -R 777 /var/www/html/storage
# RUN chmod -R 777 /var/www/html/bootstrap/cache

ARG user=library
ARG group=library
ARG uid=1000
ARG gid=1000
RUN groupadd -g ${gid} ${group}
RUN useradd -u ${uid} -g ${group} -s /bin/sh -m ${user}

USER ${uid}:${gid}

EXPOSE 9000
CMD ["php-fpm"]
