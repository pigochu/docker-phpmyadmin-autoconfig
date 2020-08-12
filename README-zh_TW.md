# docker phpmyadmin-autoconfig #

這個專案主要是使用過 [treafik](https://hub.docker.com/_/traefik) 而來的靈感，treafik 使用 labels 來達成自動設定 loading blance，我想我也可以讓 phpmyadmin 達成自動增加 db 的設定，這樣我僅需要於我的開發環境部屬一套 phpmyadmin 就能存取各專案的 mysql 了。

本專案是以官方 phpmyadmin 為基礎，使用方法除了本專案另行增加的設定方式，其他設定都與官方的一樣。



## 支援的標籤

- latest : 使用 apache  運作，不需要搭配其他 web server 即可運作
- fpm : 使用 fpm 運作，需要另外搭配其他 web server 透過 FastCGI 協定才能運作
- fpm-alpine : 使用 fpm 運作，需要另外搭配其他 web server 透過 FastCGI 協定才能運作，作業系統為 alpine , image size 小很多



## 使用docker-compose 部屬的範例 ##

原始碼中的 [example](./example) 路徑有兩個檔案可以當作測試參考，分別說明如下 :

檔案名稱 : [docker-compose.yml](./docker-compose.yml)

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

這個檔案只定義了一個服務 phpmyadmin，其中有幾點要注意的

1. volume 必須對應 /var/run/docker.sock , 因為必須監控 docker 的事件
2. networks 使用了名稱為 example_net 的自訂網路，這是為了讓 phpmyadmin 和 database 之間能夠互相通訊
3. phpmyadmin 這個服務需要定義環境變數 PHPMYADMIN_AUTOCONFIG_INSTANCE ，這代表這個服務的名稱，若沒有定義，預設名稱也是 phpmyadmin。



檔案名稱 : [docker-compose.db.yml](./docker-compose.db.yml)

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





這個範例定義了兩個服務，分別是 db1 及 db2 , 使用了官方的 mariadb 鏡像，networks 區段一樣使用了 example_net 讓 phpmyadmin 和 mariadb 能互通。

而在 db1 及 db2 中必須定義 lables, 說明如下 : 

- phpmyadmin.autoconfig.target **(必須)** : 這是定義 phpmyadmin 服務的目標名稱 , 上述已經說明要定義環境變數 **PHPMYADMIN_AUTOCONFIG_INSTANCE** 於 phpmyadmin 服務中，這個值也可以是星號 ( * )，例如 phpmyadmin.autoconfig.target=*，當你的環境使用了多個 phpmyadin-autoconfig 的 container 的時候，可以讓這些 container 一併收到事件而進行自動設定。
- phpmyadmin.autoconfig.cfg.any-key: 這是定義 [phpmyadmin server connection settings](https://docs.phpmyadmin.net/en/latest/config.html#server-connection-settings) 的參數 , 例如 : phpmyadmin.autoconfig.cfg.port=3307 or phpmyadmin.autoconfig.cfg. compress=true，那麼 phpmyadmin 服務偵測到就會自動生成這些設定值了。

### 測試方式

執行以下命令

~~~bash
docker network create --attachable -d bridge example_net
docker-compose up -d
docker-compose -f docker-composer.db.yml up -d 
~~~

本範例 phpmyadmin 使用 port 9080 , 所以開啟 http://localhost:9080 後，你可以使用帳號 root 不需要輸入密碼即可登入。

> **請注意 !! 有時候你可能會得到錯誤訊息 "Connection refused"，根據我的測試，官方的 mariadb 第一次的啟動，似乎會先進行一段初始化，需要等待個幾分鐘才能正確連線，一旦可以連線成功時，下次 restart 應該也能馬上連線成功。**



# Build Sample #
```
docker build --no-cache -t pigochu/phpmyadmin-autoconfig:dev -f build/apache/Dockerfile .
```

# 作者 #

Pigo Chu <pigochu@gmail.com>
