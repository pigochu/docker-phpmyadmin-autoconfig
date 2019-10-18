# docker phpmyadmin-autoconfig #

This project is mainly inspired by [treafik](https://hub.docker.com/_/traefik), treafik uses lables to achieve automatic setting loading blance, I think I can also let phpmyadmin achieve automatic increase of db The setting, so I only need a set of phpmyadmin in my development environment to access the mysql of each project.

This project is based on the official phpmyadmin. In addition to the additional settings of this project, the other settings are the same as the official ones.



## Supported tags

- latest : Built-in apache web server , no other web server required
- fpm : Working with fpm requires additional web server to work with the FastCGI protocol.
- fpm-alpine : Working with fpm requires additional web server to work with the FastCGI protocol. The OS is Alpine and the image size is much smaller.



## Example using docker-compose ##

The [example](./example) path in the source code has two files that can be used as test example, as explained below:

Filename : [docker-compose.yml](./docker-compose.yml)

~~~yaml
version: '3.5'
services:
        
    phpmyadmin:
        image: 'pigochu/phpmyadmin-autoconfig'
        environment:
            - PHPMYADMIN_AUTOCONFIG_INSTANCE=phpmyadmin
        ports:
              - "9080:80"
        volumes:
            - /var/run/docker.sock:/var/run/docker.sock
        networks:
            - default
            - example

# Please execute the following command
# docker network create --attachable -d bridge example_net
networks:
    example:
        external:
            name: example_net
~~~

This file only defines a service phpmyadmin, which has a few points to note.

1. Volume must map /var/run/docker.sock because the docker event must be monitored
2. Networks uses a user defined network called example_net to allow phpmyadmin and database to communicate with each other.
3. The service phpmyadmin needs to define the environment variable PHPMYADMIN_AUTOCONFIG_INSTANCE , which represents the name of the service. If not defined, the default name is also phpmyadmin.



Filename : [docker-compose.db.yml](./docker-compose.db.yml)

~~~yaml
version: '3.5'
services:
 
    db1:
        image: mariadb
        labels:
            - phpmyadmin.autoconfig.target=phpmyadmin
            - phpmyadmin.autoconfig.cfg.verbose=database-1
            - phpmyadmin.autoconfig.cfg.AllowNoPassword=true
        environment:
            - MYSQL_ALLOW_EMPTY_PASSWORD=yes
        networks:
            - default
            - example
    db2:
        image: mariadb
        labels:
            - phpmyadmin.autoconfig.target=phpmyadmin
            - phpmyadmin.autoconfig.cfg.verbose=database-2
            - phpmyadmin.autoconfig.cfg.AllowNoPassword=true
        environment:
            - MYSQL_ALLOW_EMPTY_PASSWORD=yes
        networks:
            - default
            - example

# Please execute the following command
# docker network create --attachable -d bridge example_net
networks:
    example:
        external:
            name: example_net
~~~





This example defines two services, db1 and db2, using the official mariadb image, and the networks section uses example_net to allow phpmyadmin and mariadb to communicate with each other.

Lables must be defined in db1 and db2, as follows:

- phpmyadmin.autoconfig.target **(required)** : This is the target name that defines the phpmyadmin service. The above has been explained to define the environment variable **PHPMYADMIN_AUTOCONFIG_INSTANCE** in the phpmyadmin service. This value can also be an asterisk ( * ), for example phpmyadmin.autoconfig.target=*, when your environment uses When multiple phpmyadin-autoconfig containers are used, these containers can be automatically set by receiving events.
- This is the parameter that defines phpmyadmin connection , for example: phpmyadmin.autoconfig.cfg.port=3307 or phpmyadmin.autoconfig.cfg. compress=true, then the phpmyadmin service will automatically generate these settings when it detects it.

### Test it !

Execute the following command

~~~bash
docker network create --attachable -d bridge example_net
docker-compose up -d
docker-compose -f docker-composer.db.yml up -d 
~~~

This service phpmyadmin uses port 9080 , so after opening http://localhost:9080, you can use the account root to login without entering a password.

> **Attention!! Sometimes you may get the error message "Connection refused". According to my test, when started  the official MariaDb, it seems that it will be initialized first, and it takes a few minutes to connect correctly. Once it can be connected after few minutes, and next time restart container should also be connected successful.**





# Author #

Pigo Chu <pigochu@gmail.com>