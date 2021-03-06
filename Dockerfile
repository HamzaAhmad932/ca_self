FROM php:7

RUN apt-get update -y && apt-get install -y openssl zip unzip git vim iputils-ping wget lsb-release gnupg procps nodejs sudo
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN docker-php-ext-install pdo pdo_mysql mbstring
WORKDIR /app
COPY . /app
RUN composer install

ENV MYSQL_MAJOR 5.7
ENV MYSQL_VERSION 5.7.24-1debian9

RUN echo "deb http://repo.mysql.com/apt/debian/ stretch mysql-${MYSQL_MAJOR}" > /etc/apt/sources.list.d/mysql.list

RUN apt-get update -y
# RUN apt-get install -y mysql-server
RUN apt-get install -y --allow-unauthenticated mysql-community-client="${MYSQL_VERSION}"

RUN curl -sL https://deb.nodesource.com/setup_11.x | sudo -E bash -
RUN apt-get install -y nodejs
RUN npm install npm@latest -g
RUN npm audit fix --force
RUN npm install
RUN php artisan config:cache
# RUN php artisan migrate

CMD php artisan serve --host=0.0.0.0 --port=8181
EXPOSE 8181