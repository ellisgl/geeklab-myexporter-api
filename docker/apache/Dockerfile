FROM httpd:2.4.57-alpine3.17

RUN apk update; \
    apk upgrade;
# Copy apache vhost file to proxy php requests to php-fpm container
COPY api.apache.conf /usr/local/apache2/conf/api.apache.conf
RUN echo "Include /usr/local/apache2/conf/api.apache.conf" \
    >> /usr/local/apache2/conf/httpd.conf
