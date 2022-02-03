FROM 'php:7.3-apache'

RUN apt-get update

RUN set -ex apk --no-cache add postgresql-dev libpq-dev
RUN apt-get install -y vim htop libpq-dev libonig-dev ssmtp git
RUN docker-php-ext-install mbstring pdo_mysql opcache
RUN apt-get install -y msmtp msmtp-mta unzip

# openssl対応
RUN apt-get install -y libssl-dev \
    openssl \
    ssl-cert
RUN apt-get clean
RUN rm -rf /var/lib/apt/lists/* \
    && a2enmod ssl \
    && a2ensite default-ssl

# M1Mac対応
# M1MacでのDocker起動時はコメントアウトを解除
#RUN echo "ServerName localhost" | tee /etc/apache2/conf-available/fqdn.conf
#RUN a2enconf fqdn

# modsecurity 導入
RUN apt update
RUN apt install -y libapache2-mod-security2

COPY ./docker/app/conf/etc/modsecurity/modsecurity.conf /etc/modsecurity/

# modsecurity-crs
RUN mv /usr/share/modsecurity-crs /usr/share/modsecurity-crs.bk
RUN git clone https://github.com/SpiderLabs/owasp-modsecurity-crs.git /usr/share/modsecurity-crs
RUN cp /usr/share/modsecurity-crs/crs-setup.conf.example /usr/share/modsecurity-crs/crs-setup.conf

COPY ./docker/app/conf/etc/apache2/mods-available/security2.conf /etc/apache2/mods-available/security2.conf
RUN a2enmod security2

# mod_rewrite有効化
RUN a2enmod rewrite

# proxy
COPY ./docker/app/conf/etc/apache2/mods-available/proxy.conf /etc/apache2/mods-available/proxy.conf
RUN a2enmod proxy proxy_http lbmethod_byrequests


# ログ調べ
COPY ./docker/app/conf/etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf

RUN a2ensite 000-default

RUN apt-get install -y libzip-dev
# composer-setup.php ハッシュ名
ARG composer_setup_hash=906a84df04cea2aa72f40b5f787e49f22d4c2f19492ac310e8cba5b96ac8b64115ac402c8cd292b8a03482574915d1a8
# インストール後の Composer のフォルダ名とファイル名
ARG composer_dir=/usr/local/bin
ARG composer_filename=composer

# Composer インストール
COPY --from=composer /usr/bin/composer /usr/bin/composer

# /var/www/html マウント
COPY ./docker/app/src/ /var/www/html/
# COPY ./src/ /var/www/html/

# composer 用意
COPY ./docker/app/conf/composer.json /var/www/html
RUN composer update
RUN composer install

# httpd.conf, php.ini を読み込ませる
COPY ./docker/app/conf/etc/apache2/apache2.conf /etc/apache2/
COPY ./docker/app/conf/php.ini /usr/local/etc/php/

# xdebug インストール
RUN pecl install xdebug && docker-php-ext-enable xdebug

# MSMTPの設定ファイルを読ませる
COPY ./docker/app/conf/msmtprc /etc/msmtprc
# パーミッション設定
RUN chown www-data:www-data /etc/msmtprc
RUN chmod 600 /etc/msmtprc

# aliasesを読み込ませる
COPY ./docker/app/conf/aliases /etc/aliases
RUN chmod 644 /etc/aliases

# ドキュメントルート設定（httpd.conf:debian系で不可）
ENV APACHE_DOCUMENT_ROOT /var/www/html/app
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/conf-available/*.conf

# セキュリティ設定
# mod_headers 有効化
RUN a2enmod headers
# security.conf ファイルを読み込ませる
COPY ./docker/app/conf/etc/apache2/conf-available/security.conf /etc/apache2/conf-available/security.conf


RUN apachectl restart