# SOZOTOWN
創造社デザイン専門学校  
情報セキュリティ管理者資格コース二年  
卒業WS_CTF課題対応作品

# DEMO
![SOZOTOWN_DEMO.gif](/README_img/SOZOTOWN_DEMO.gif) 

# Document
* Git運用資料  
![GitHub_operation.pdf](/README_doc/GitHub_operation.pdf)  
* FE_画面仕様書  
![画面仕様書.pdf](/README_doc/画面仕様書.pdf)  
* FE_画面遷移図  
![画面遷移図.pdf](/README_doc/画面遷移図.pdf)  
* DB_ユースケース図  
![ユースケース図.pdf](/README_doc/ユースケース図.pdf)  
* DB_ER図  
![ER図.pdf](/README_doc/ER図.pdf)  
* DB_テーブル定義書  
![テーブル定義書.pdf](/README_doc/テーブル定義書.pdf)  
* IF_物理構成図  
![物理構成図.pdf](/README_doc/物理構成図.pdf)  
* IF_論理構成図  
![論理構成図.pdf](/README_doc/論理構成図.pdf)  
* QA_テスト仕様書  
![テスト仕様書.pdf](/README_doc/テスト仕様書.pdf)  

# Requirement
* Frontend
    * HTML / CSS / Javascript
* Backend
    * PHP(smarty/phpdotenv/valitron/fast-route/faker)
* Infrastructure
    * Docker(Docker-compose)
        * Apache(modsecurity)
        * Nginx(Proxy)
        * MySQL

# Author
* Project_manager
    * M K ※
* Project_leader
    * Y C ※

* Frontend_team
    * S D ※ (Leader)
    * R E
    * S K
    * S T ※
* Backend_team
    * Y K ※ (Leader)
    * S D ※
    * T N
    * N Y
* database_team
    * Y F ※ (Leader)
    * M K ※
    * Y K ※
    * R M ※
    * T Y
* Infrastructure_team
    * R M ※ (Leader)
    * Y C ※
    * Y F ※
* QualityAssurance_team
    * M K ※ (Leader)
    * M K ※
    * S T ※
    * Y Y

# Installation
## Single_Server
1. `git clone https://github.com/makoto-kamimura/SOZOTOWN.git`
2. SOZOTOWN/docker/app/src/.env を作成
3. SOZOTOWN/yml/ にて、`docker-compose up -d --build`
4. `docker container exec -it sozotown_app_1 bash` でappコンテナに入る
5. `php /var/www/html/test/faker.php`でダミーデータ生成
6. `http://localhost:18080`で確認

## Multiple_Server
### WEB_Server
1. /opt にて、`git clone https://github.com/makoto-kamimura/SOZOTOWN.git`
2. SOZOTOWN/docker/app/src/.env を作成
3. SOZOTOWN/yml/ にて、`docker network create db-network`, `docker network create proxy-network`
4. `docker-compose -f docker-compose.app.yml up -d --build`
5. `docker container exec -it sozotown_app_1 bash` でappコンテナに入る
6. `mkdir /var/www/html/app/templates_c`でディレクトリの作成
7. `chmod 777 /var/www/html/app/templates_c`で権限変更
8. `php /var/www/html/test/faker.php`でダミーデータ生成
9. ブラウザで確認

### DB_Server
1. /opt にて、`git clone https://github.com/makoto-kamimura/SOZOTOWN.git`
2. SOZOTOWN/yml/ にて、`docker network create db-network`
3. `docker-compose -f docker-compose.db.yml up -d --build`
4. ブラウザで確認

### PROXY_Server
1. /opt にて、`git clone https://github.com/makoto-kamimura/SOZOTOWN.git`
2. SOZOTOWN/yml/ にて、`docker network create proxy-network`
3. `docker-compose -f docker-compose.proxy.yml up -d --build`
4. ブラウザで確認

### MAIL_Server(仮実装)
1. /opt にて、`git clone https://github.com/makoto-kamimura/SOZOTOWN.git`
2. SOZOTOWN/yml/ にて、`docker network create mail-network`
3. `docker-compose -f docker-compose.mail.yml up -d --build`
4. ブラウザで確認

# License
https://choosealicense.com/no-permission/
