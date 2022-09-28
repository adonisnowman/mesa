# Phalcon-admin

Phalcon-admin

## Getting started

NGINX
PHP 7.4
MYSQL 8
PHPMYADMIN
PHALCON 4
PSR 1.2
UBUNTU 20.04

## Add your SQL

- 
- Mysql 需要設定 大小寫判斷 密碼強度需要設定為STRONG
- 設定相關的DB 連線 /phalcon/config/config.ini
- 
- [ ] [phalcon4.sql]
- add nginx conf 
- https://docs.phalcon.io/4.0/en/webserver-setup
```
    root /var/www/default/public;
    index index.php index.html index.htm;

    charset utf-8;
    client_max_body_size 100M;
    fastcgi_read_timeout 1800;

    # Represents the root of the domain
    # https://localhost:8000/[index.php]
    location / {
        # Matches URLS `$_GET['_url']`
        try_files $uri $uri/ /index.php?_url=$uri&$args;
    }

    location ~ [^/]\.php(/|$) {
        .....
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
```

## Start Your Web Admin

- [ ] [Set config.ini > init > reset = 1 ]
- [ ] [Set config.ini > domain > dev = your.domain.com ]
try to link your.domain.com
if it's done ,
- [ ] [Set config.ini > init > reset = 0 ]
- [ ] [SignUp the Admin account By First Login ex: admin / admin ]