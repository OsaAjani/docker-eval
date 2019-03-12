<?php
    $stop_docker = 'docker stop';
    $rm_docker = 'docker rm';
    for ($i = 1; $i <= 30; $i++)
    {
        $stop_docker .= ' b2_correct_' . $i;
        $rm_docker .= ' b2_correct_' . $i;
    }
    
    echo($stop_docker."\n");
    exec($stop_docker);
    echo($rm_docker."\n");
    exec($rm_docker);

    

