FROM  php:8.2-apache


COPY ./src/index.php /var/www/html/
RUN mkdir /var/www/resources
COPY ./resources/config.ini /var/www/resources/

EXPOSE 80