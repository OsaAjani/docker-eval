update mysql.user set plugin = 'mysql_native_password' where User='root';
FLUSH PRIVILEGES;
CREATE USER 'root'@'%' IDENTIFIED BY 'bernardbernard';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%'
FLUSH PRIVILEGES;
