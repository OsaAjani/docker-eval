#!/bin/bash

if [ ! -f /first_install.lock ] ; then
    
    #Lock first install
    touch /first_install.lock


    #Make mysql user
    /etc/init.d/mysql restart
    mysql -u root < /mariadb_root.sql
    
    
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
