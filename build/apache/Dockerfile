FROM phpmyadmin/phpmyadmin:latest

ENV PHPMYADMIN_AUTOCONFIG_INSTANCE=phpmyadmin

COPY script /opt/phpmyadmin-autoconfig

RUN docker-php-ext-install pcntl && \
chmod +x /opt/phpmyadmin-autoconfig/*.sh

ENTRYPOINT [ "/opt/phpmyadmin-autoconfig/entrypoint.sh" ]
CMD ["apache2-foreground"]