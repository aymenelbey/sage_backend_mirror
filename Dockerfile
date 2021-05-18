FROM php:7.4-fpm as base
WORKDIR /app
COPY . /app
EXPOSE 8000
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN apt-get update && apt-get install -y libpq-dev
RUN docker-php-ext-install pdo pdo_pgsql pgsql
RUN ln -s /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
RUN sed -i -e 's/;extension=pgsql/extension=pgsql/' /usr/local/etc/php/php.ini
RUN sed -i -e 's/;extension=pdo_pgsql/extension=pdo_pgsql/' /usr/local/etc/php/php.ini
RUN composer install
#FROM base as production
#ENV NODE_ENV=production

#RUN npm run build
#CMD ["node", "build/"]

FROM base as development
ENV NODE_ENV=development
CMD php artisan serve