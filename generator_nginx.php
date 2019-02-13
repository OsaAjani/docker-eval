<?php
$text = '';
for ($i = 1; $i <= 65000; $i++)
{
    $text .= '
server {
    listen 80;
    server_name '.$i.'.plebweb.fr;

    location / { 
        proxy_pass         http://0.0.0.0:'.$i.';
        proxy_redirect     off;
        proxy_set_header   Host $host;
        proxy_set_header   X-Real-IP $remote_addr;
        proxy_set_header   X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Host $server_name;
    }   
}

';

}
    echo $text;



