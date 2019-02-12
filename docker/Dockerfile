FROM ubuntu:bionic

ENV DEBIAN_FRONTEND noninteractive
RUN export DEBIAN_FRONTEND=noninteractive

RUN apt update -y && apt install -y vim apache2 php php-xml php-mbstring php-pdo php-mysql supervisor mariadb-server composer
RUN a2enmod rewrite
COPY 000-default.conf /etc/apache2/sites-enabled/000-default.conf

WORKDIR /var/www/html/httpstatus

COPY ./entrypoint.sh /
COPY ./mariadb_root.sql /

CMD service mysql start && tail -f /var/log/apache2/error.log

ENTRYPOINT ["/entrypoint.sh"]
