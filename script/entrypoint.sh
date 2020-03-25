#!/bin/bash
echo "PHPMYADMIN_AUTOCONFIG_BIN_VERSION $PHPMYADMIN_AUTOCONFIG_BIN_VERSION"
if [ "$PHPMYADMIN_AUTOCONFIG_BIN_VERSION" = "go" ]; then
    /opt/phpmyadmin-autoconfig/docker-phpmyadmin-autoconfig &
else
    php /opt/phpmyadmin-autoconfig/main.php &
fi

exec /docker-entrypoint.sh "$@"
