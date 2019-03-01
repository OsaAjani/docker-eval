<?php
    //Run bots
    for ($i = 1; $i <= 10; $i++)
    {
        //Run docker
        echo "Running correction for b2_correct_" . $i . "...\n";
        $port = 8000 + $i;
        $run_docker = 'docker exec b2_correct_' . $i . ' sh -c "php /var/www/html/bot.php > /var/www/html/note.txt" &';
        #echo($run_docker."\n");
        exec($run_docker);
    }

    sleep(30);
    //sleep(60 * 15); //wait for 15 minutes to make sure everything execute correctly
 
    for ($i = 1; $i <= 10; $i++)
    {
        echo "Show note for b2_correct_$i\n";
        echo "----------------------------\n";
        $run_docker = 'docker exec b2_correct_' . $i . ' cat /var/www/html/note.txt';
        $output = null;
        exec($run_docker, $output);
        echo join("\n", $output);
        echo "\n---------------------------\n\n";
    } 

