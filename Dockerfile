FROM ubuntu:bionic

ARG HTML_PATH='/var/www/html/'
ARG HTTPSTATUS_PATH='/var/www/html/httpstatus'

ENV DEBIAN_FRONTEND noninteractive
RUN export DEBIAN_FRONTEND=noninteractive

RUN apt update -y && apt install -y vim apache2 php php-xml php-mbstring php-pdo php-mysql php-curl php-zip supervisor mariadb-server composer
RUN a2enmod rewrite
COPY ./data/000-default.conf /etc/apache2/sites-enabled/000-default.conf


WORKDIR $HTTPSTATUS_PATH


COPY ./data/entrypoint.sh /
COPY ./data/mariadb_root.sql /

CMD service mysql start ; /etc/init.d/supervisor start ; tail -f /var/log/apache2/error.log

ENTRYPOINT ["/entrypoint.sh"]
