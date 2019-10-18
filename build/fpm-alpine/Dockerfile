FROM phpmyadmin/phpmyadmin:fpm-alpine

ENV PHPMYADMIN_AUTOCONFIG_INSTANCE=phpmyadmin

COPY script /opt/phpmyadmin-autoconfig

RUN  apk update; \
        apk upgrade; \
        docker-php-ext-install pcntl; \
        chmod +x /opt/phpmyadmin-autoconfig/*.sh

ENTRYPOINT [ "/opt/phpmyadmin-autoconfig/entrypoint.sh" ]
CMD ["php-fpm"]