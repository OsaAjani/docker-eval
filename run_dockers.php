<?php
    $i = 0;

    $repos_file = __DIR__ . '/repos.txt';
    $handle = fopen($repos_file, "r");

    if (!$handle)
    {
        echo "Cannot open file " . $repos_file . "\n";
        exit(1);
    }

    while (($line = fgets($handle)) !== false) 
    {
        $i++;

        if ($i > 30)
        {
            echo "Stop at 30 running containers...\n";
            break;
        }

        $repo = $line;

        //Run docker
        echo "Running b2_correct_" . $i . "...\n";
        $port = 8000 + $i;
        $run_docker = 'docker run -d -p ' . $port . ':80 -e "GIT=' . $repo . '" --cpus=0.2 -m=800m --name=b2_correct_' . $i . ' b2_correct';
        #echo($run_docker."\n");
        exec($run_docker);

        sleep(3); //sleep 3 seconds
    }

    

