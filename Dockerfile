FROM php:8.3-apache

# Extensões PHP necessárias
RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite

# Configuração do PHP
RUN { \
      echo 'date.timezone = America/Sao_Paulo'; \
      echo 'display_errors = On'; \
      echo 'error_reporting = E_ALL'; \
    } > /usr/local/etc/php/conf.d/app.ini

# O DocumentRoot aponta para a pasta public (front controller)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && sed -ri -e 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

COPY src/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
