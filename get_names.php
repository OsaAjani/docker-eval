<?php
    //Run bots
    for ($i = 1; $i <= 30; $i++)
    {
        //Run docker
        $port = 8000 + $i;
        $status = null;
        $output = null;
        $run_docker = 'docker exec b2_correct_' . $i . ' sh -c \'cat /var/www/html/httpstatus/names.txt\'';
        exec($run_docker, $output, $status);
        echo implode(" - ", $output);
        echo "\n\n";
    }


