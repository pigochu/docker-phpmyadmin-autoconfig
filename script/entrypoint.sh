#!/bin/bash

php /opt/phpmyadmin-autoconfig/main.php &
exec /docker-entrypoint.sh "$@"
