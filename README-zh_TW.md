# docker phpmyadmin-autoconfig #

這個專案主要是使用過 [treafik](https://hub.docker.com/_/traefik) 而來的靈感，treafik 使用 lables 來達成自動設定 loading blance，我想我也可以讓 phpmyadmin 達成自動增加 db 的設定，這樣我僅需要於我的開發環境部屬一套 phpmyadmin 就能存取各專案的 mysql 了。



## 使用docker-compose 部屬的範例 ##

請參考範例檔案 : [docker-compose.example.yml](./docker-compose.example.yml)

~~~yaml
version: '3.5'
services:
        
    myadmin:
        image: 'pigochu/phpmyadmin-autoconfig'
        environment:
            - PHPMYADMIN_AUTOCONFIG_INSTANCE=phpmyadmin
        ports:
              - "9080:80"
        volumes:
            - /var/run/docker.sock:/var/run/docker.sock
 
    db1:
        image: mariadb
        labels:
            - phpmyadmin.autoconfig.target=phpmyadmin
            - phpmyadmin.autoconfig.cfg.verbose=database-1
            - phpmyadmin.autoconfig.cfg.AllowNoPassword=true
        environment:
            - MYSQL_ALLOW_EMPTY_PASSWORD=yes
    db2:
        image: mariadb
        labels:
            - phpmyadmin.autoconfig.target=phpmyadmin
            - phpmyadmin.autoconfig.cfg.verbose=database-2
            - phpmyadmin.autoconfig.cfg.AllowNoPassword=true
        environment:
            - MYSQL_ALLOW_EMPTY_PASSWORD=yes
~~~

這個範例定義了三個服務，分別是 myadmin 使用 pigochu/phpmyadmin-autoconfig 的 Image，及 db1 和 db2 使用了官方 mariadb 的 Image，藉由 lables 設定，讓 phpmyadmin 能夠自動生成多伺服器的設定檔。

### 測試方式

執行以下命令

~~~bash
docker-composer -f docker-composer.example.yml up -d 
~~~

本範例使用 port 9080 , 所以開啟 http://localhost:9080 後，你可以使用帳號 root 不需要輸入密碼即可登入 , 但有時候你可能會得到錯誤訊息 "Connection refuse" , 因為我是在 Windows 10 WSL2 mode 測試中發現，有可能 mariadb 沒辦法正確的 bind port 3306，需要等上 1 分鐘以上才能登入，這並不是 phpmyadmin的 bug，所以若您發生這種問題，請有點耐心。



範例中 myadmin 這個服務需要定義環境變數 PHPMYADMIN_AUTOCONFIG_INSTANCE ，這代表這個服務的名稱，若沒有定義，預設名稱也是 phpmyadmin。



而在 db1 及 db2 中需要定義 lables, 說明如下 : 

- phpmyadmin.autoconfig.target : 這是定義 phpmyadmin 服務的目標名稱 , 上述已經說明要定義環境變數 **PHPMYADMIN_AUTOCONFIG_INSTANCE** 於 myadmin 服務中，這個值也可以是星號 ( * )，例如 phpmyadmin.autoconfig.target=*，當你的環境使用了多個 phpmyadin-autoconfig 的 container 的時候，可以讓這些 container 一併收到事件而進行自動設定。
- phpmyadmin.autoconfig.cfg.any-key: 這是定義 [phpmyadmin server connection settings](https://docs.phpmyadmin.net/en/latest/config.html#server-connection-settings) 的參數 , 例如 : phpmyadmin.autoconfig.cfg.port=3307 or phpmyadmin.autoconfig.cfg. compress=true，那麼 myadmin服務偵測到就會自動生成這些設定值了。

### 最後請注意 !!!

由於範例是把 phpmyadmin 及 db 寫在同一個 docker-compose.example.yml 中，因此網路區段是一樣的，一般我們部屬不同應用時網路區段可能會隔開，因此若要讓 phpmyadmin 能夠正常連線到 db，必須另外建立一個共用的網路區段才可能正常連線。



# 作者 #

Pigo Chu <pigochu@gmail.com>