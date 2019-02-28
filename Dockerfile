FROM ubuntu:bionic

ARG HTML_PATH='/var/www/html/'
ARG HTTPSTATUS_PATH='/var/www/html/httpstatus'

ENV DEBIAN_FRONTEND noninteractive
RUN export DEBIAN_FRONTEND=noninteractive

RUN debconf-set-selections <<< "postfix postfix/main_mailer_type select Satellite system"
RUN debconf-set-selections <<< "postfix postfix/mailname string $HOSTNAME"
RUN debconf-set-selections <<< "postfix postfix/relayhost string smtp.mailgun.org"

RUN apt update -y && apt install -y vim apache2 php php-xml php-mbstring php-pdo php-mysql php-curl php-zip supervisor mariadb-server composer postfix mailutils
RUN a2enmod rewrite
COPY ./data/000-default.conf /etc/apache2/sites-enabled/000-default.conf

RUN echo "smtp.mailgun.org postmaster@sandboxcbfbdc00f6834151ad21891675f9c335.mailgun.org:35bd692bb744831e17857f709414d5d0" > /etc/postfix/sasl_passwd
RUN chmod 600 /etc/postfix/sasl_passwd
RUN postmap /etc/postfix/sasl_passwd
RUN echo "smtp_sasl_auth_enable = yes
smtp_sasl_password_maps = hash:/etc/postfix/sasl_passwd
smtp_sasl_security_options = noanonymous
smtp_sasl_tls_security_options = noanonymous
smtp_sasl_mechanism_filter = AUTH LOGIN" >> /etc/postfix/main.cf



WORKDIR $HTTPSTATUS_PATH


COPY ./data/entrypoint.sh /
COPY ./data/mariadb_root.sql /

CMD service mysql start ; /etc/init.d/supervisor start ; tail -f /var/log/apache2/error.log

ENTRYPOINT ["/entrypoint.sh"]
