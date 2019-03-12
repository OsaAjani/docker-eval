#!/bin/bash

/etc/init.d/mysql restart
/etc/init.d/apache2 restart
/etc/init.d/supervisor restart
/etc/init.d/postfix restart


if [ ! -f /first_install.lock ] ; then
    
    #Lock first install
    touch /first_install.lock


    #Make mysql user
    mysql -u root < /mariadb_root.sql


    #Clone github
    git clone $GIT /var/www/html/httpstatus/
    sed -i -e "s/'' : \$_SERVER/'' : ':' . \$_SERVER/g" /var/www/html/httpstatus/descartes/env.php

    #Copy .htaccess default if .htaccess does not exist
    if [ ! -f /var/www/html/httpstatus/.htaccess && ! -f /var/www/html/httpstatus/symfony.lock ] ; then
        cp /var/www/html/.htaccess.default /var/www/html/httpstatus/.htaccess
    fi
   
    #Change user
    random=$(cat /dev/urandom | tr -dc 'a-z0-9' | fold -w 8 | head -n 1)
    echo "deschaussettes$random@yopmail.com" > /var/www/html/httpstatus/mail.txt
    find /var/www/html/httpstatus/ -type f -not -path '*.git*' -not -path '*vendor*' -exec sed -i -e "s/deschaussettes@yopmail.com/deschaussettes$random@yopmail.com/g" {} \;

    #Create database
    if [ -f /var/www/html/httpstatus/create_database.sql ] ; then
        mysql -u root -pbernardbernard < /var/www/html/httpstatus/create_database.sql
    fi
    
    #Composer install
    cd /var/www/html/httpstatus/ && composer install


    #Copy supervisor.conf
    if [ -f /var/www/html/httpstatus/supervisor.conf ] ; then
        cp /var/www/html/httpstatus/supervisor.conf /etc/supervisor/conf.d/
        /etc/init.d/supervisor restart
    fi

    #Make chmod 777 to prevent any problems
    chmod -R 777 /var/www/html/

fi

exec "$@"
