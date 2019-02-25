#!/bin/bash

/etc/init.d/mysql restart
/etc/init.d/apache2 restart
/etc/init.d/supervisor restart


if [ ! -f /first_install.lock ] ; then
    
    #Lock first install
    touch /first_install.lock


    #Make mysql user
    mysql -u root < /mariadb_root.sql


    #Clone github
    git clone $GIT /var/www/html/httpstatus/
    sed -i -e "s/'' : \$_SERVER/'' : ':' . \$_SERVER/g" /var/www/html/httpstatus/descartes/env.php

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
