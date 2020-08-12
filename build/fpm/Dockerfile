# build stage
FROM golang:1.15-buster as builder
RUN mkdir /src
WORKDIR /src
COPY . /src
RUN go build -ldflags "-s -w"

# final stage

FROM phpmyadmin/phpmyadmin:fpm
ENV PHPMYADMIN_AUTOCONFIG_BIN_VERSION=go
ENV PHPMYADMIN_AUTOCONFIG_INSTANCE=phpmyadmin

COPY script /opt/phpmyadmin-autoconfig
COPY --from=builder /src/docker-phpmyadmin-autoconfig /opt/phpmyadmin-autoconfig

RUN docker-php-ext-install pcntl && \
    chmod +x /opt/phpmyadmin-autoconfig/*.sh && \
    chmod +x /opt/phpmyadmin-autoconfig/docker-phpmyadmin-autoconfig

ENTRYPOINT [ "/opt/phpmyadmin-autoconfig/entrypoint.sh" ]
CMD ["php-fpm"]