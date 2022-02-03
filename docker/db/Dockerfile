FROM 'mysql:5.7'

RUN apt-get update
RUN apt-get install -y python-pip python3-pip locales \
    && locale-gen ja_JP.UTF-8
RUN pip3 install mycli

COPY ./docker/db/conf/my.cnf /etc/mysql/conf.d/my.cnf

ENV LANG ja_JP.UTF-8
ENV LANGUAGE ja_JP:ja
ENV LC_ALL=ja_JP.UTF-8
RUN localedef -f UTF-8 -i ja_JP ja_JP.utf8

COPY ./docker/db/initdb.d /docker-entrypoint-initdb.d
