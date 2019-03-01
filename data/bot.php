<?php

/** Tools **/
function unparse_url($parsed_url) 
{
    $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
    $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
    $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
    $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
    $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
    $pass     = ($user || $pass) ? "$pass@" : '';
    $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
    $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
    return "$scheme$user$pass$host$port$path$query$fragment";
}

function array_keys_exists($keys, $array)
{
    foreach ($keys as $key)
    {
        if (!array_key_exists($key, $array))
        {
            return false;
        }
    }
    return true;
}

function query ($url, $port = false, $get = [], $post = false)
{
    $url = parse_url($url);

    #Get params from url
    $url_get = [];

    if (isset($url['query']))
    {
        parse_str($url['query'], $url_get);
    }

    if ($port)
    {
        $url['port'] = $port;
    }

    #Use api key : url -> params -> default
    $get['api_key'] = $url_get['api_key'] ?? $get['api_key'] ?? 'abcdefghjaimelesapis';
    $params = array_merge($url_get, $get);

    #forge url
    $url['path'] = rtrim($url['path'], '/');
    $url['query'] = http_build_query($params);
    $url = unparse_url($url);

    #make query
    $url = $url;

    // Initializing curl
    $ch = curl_init($url);

    // Configuring curl options
    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
    );

    if ($post)
    {
        $options[CURLOPT_POST] = count($post);
        $options[CURLOPT_POSTFIELDS] = http_build_query($post);
    }

    // Setting curl options
    curl_setopt_array($ch, $options);

    // Getting results
    $result = curl_exec($ch); // Getting jSON result string
    $json = json_decode($result, true);

    if (!$json)
    {
        return false;
    }

    return $json;
}

function show_note ($project)
{
    $names = file_get_contents(__DIR__ . '/httpstatus/names.txt');
    if (!$names)
    {
        echo "Impossible to find names.\n";
        exit(1);
    }

    echo    $names . 
            "\n---------------\n" . 
            ($project['note'] ?? 0) . "/15\n\n";
    exit(0);
}


/** Actions **/
function get_root (&$project)
{
    $json = query($project['url']);

    if (empty($json['list']))
    {
        return false;
    }

    $project['urls'] = ['list' => $json['list']];
    $project['note'] += 1;
    return true;
}


function add_websites (&$project)
{
    $api_url = 'http://127.0.0.1/httpstatus/api/add';
    $json_valid_website = query($api_url, false, [], ['url' => 'http://raspbian-france.fr']);
    $json_invalid_website = query($api_url, false, [], ['url' => 'http://donotexist.raspbian-france.fr']);
    $json_todelete_website = query($api_url, false, [], ['url' => 'http://todelete.raspbian-france.fr']);

    $project['valid_website'] = isset($json_valid_website['id']) ? ['id' => $json_valid_website['id']] : false;
    $project['invalid_website'] = isset($json_invalid_website['id']) ? ['id' => $json_invalid_website['id']] : false;
    $project['todelete_website'] = isset($json_todelete_website['id']) ? ['id' => $json_todelete_website['id']] : false;

    if (!($project['valid_website'] && $project['invalid_website'] && $project['todelete_website']))
    {
        return false;
    }

    $project['note'] += 2;

    return true;
}


function get_list (&$project)
{
    $api_url = $project['urls']['list'];

    $json = query($api_url);

    if (empty($json['websites']))
    {
        return false;
    }

    
    $find_website = 0;
    $website_ids = [
        $project['valid_website']['id'],
        $project['invalid_website']['id'],
        $project['todelete_website']['id'],
    ];
    foreach ($json['websites'] as $website)
    {
        if (in_array($website['id'], $website_ids))
        {
            $find_website += 1;
        }
    }

    if ($find_website < 3)
    {
        return false;
    }

    $project['note'] += 1;


    $pts_all_urls = 1;
    foreach ($json['websites'] as $website)
    {
        if (!isset($website['id'], $website['url'], $website['delete'], $website['status'], $website['history']))
        {
            $pts_all_urls = 0;
        }

        if (!isset($website['id']))
        {
            return false;
        }

        $api_urls = [
            'delete' => $website['delete'],
            'status' => $website['status'],
            'history' => $website['history'],
        ];

        switch ($website['id'])
        {
            case $project['valid_website']['id'] :
                $project['valid_website']['api_urls'] = $api_urls;
                $project['valid_website']['url'] = $website['url'];
                break;

            case $project['invalid_website']['id'] :
                $project['invalid_website']['api_urls'] = $api_urls;
                $project['invalid_website']['url'] = $website['url'];
                break;

            case $project['todelete_website']['id'] :
                $project['todelete_website']['api_urls'] = $api_urls;
                $project['todelete_website']['url'] = $website['url'];
                break;
        }
    }

    $project['note'] += $pts_all_urls;
    return true;
}


function delete_website (&$project)
{
    $api_url = $project['todelete_website']['api_urls']['delete'] ?? false;

    if (!$api_url)
    {
        return false;
    }

    //call delete
    query($api_url); 

    //search for this website in list
    $json = query($project['urls']['list']);

    foreach ($json['websites'] as $website)
    {
        if ($website['id'] == $project['todelete_website']['id'])
        {
            return false;
        }
    }

    $project['note'] += 2;
    return true;
}


function get_websites_status (&$project)
{
    $websites = [
        ['key' => 'valid_website', 'expected_status' => 200],
        ['key' => 'invalid_website', 'expected_status' => 999],
    ];

    $status_ok = true;
    $format_ok = true;
    foreach ($websites as $website)
    {
        $api_url = $project[$website['key']]['api_urls']['status'];
        $json = query($api_url);

        if (!$json)
        {
            return false;
        }
        
        if (!array_key_exists('status', $json) || !array_key_exists('code', $json['status']) || $json['status']['code'] != $website['expected_status'])
        {
            $status_ok = false;
        }

        if (!array_keys_exists(['id', 'url', 'status'], $json) || !array_keys_exists(['code', 'at'], $json['status']))
        {
            $format_ok = false;
        }
    }

    if ($format_ok)
    {
        $project['note'] += 1;
    }
    
    if ($status_ok)
    {
        $project['note'] += 2; 
    }

    return true;
}


function get_history (&$project)
{
    $websites = [
        ['key' => 'valid_website', 'expected_status' => 200],
        ['key' => 'invalid_website', 'expected_status' => 999],
    ];

    $status_ok = true;
    $format_ok = true;
    $date_ok = true;
    foreach ($websites as $website)
    {
        $api_url = $project[$website['key']]['api_urls']['history'];
        $json = query($api_url);

        if (!$json || !isset($json['status']))
        {
            return false;
        }

        if (count($json['status']) < 3)
        {
            $status_ok = false;
        }

        $prev_date = null;
        foreach ($json['status'] as $status)
        {
            if (!isset($status['code']) || $status['code'] != $website['expected_status'])
            {
                $status_ok = false;
            }

            if ($prev_date !== null)
            {
                $diff = $prev_date->diff(DateTime::createFromFormat('T-m-d H:i:s', $status['at'])); 
                $diff_to_sec = $diff->i * 60 + $diff->s;
                $diff_to_sec = abs($diff_to_sec);

                if ($diff_to_sec < 110 || $diff_to_sec > 130)
                {
                    $date_ok = false;
                }
            }

            if (!isset($status['code'], $status['at']))
            {
                $format_ok = false;
            }

            $prev_date =  DateTime::createFromFormat('T-m-d H:i:s', $status['at']) ?? false;
            if ($prev_date === false)
            {
                $date_ok = false;
            }
        }

        if (!isset($json['id'], $json['url'], $json['status']))
        {
            $format_ok = false;
        }
    }

    if ($format_ok)
    {
        $project['note'] += 1;
    }
    
    if ($date_ok)
    {
        $project['note'] += 1;
    }
    
    if ($status_ok)
    {
        $project['note'] += 1; 
    }

    return true;
}

function verif_mails (&$project)
{
    $yopmail_address = file_get_contents(__DIR__ . '/httpstatus/mail.txt');
    if (!$yopmail_address)
    {
        return false;
    }

    $yopmail_url = 'http://www.yopmail.com/inbox.php?v=2.9&login=' . rawurlencode(explode('@', $yopmail_address)[0]);
    $yopmail_page = file_get_contents($yopmail_url);

    if (!$yopmail_page)
    {
        return false;
    }

    $find_mail = mb_strpos($yopmail_page, 'class="um"');
    if ($find_mail === false)
    {
        return false;
    }

    $project['note'] += 2;
    return true;
}

/* Main */

$project = [
    'url' => 'http://127.0.0.1/httpstatus/api',
    'note' => 0,
];

$get_root = get_root($project);
if (!$get_root)
{
    show_note($project);
}

$add_websites = add_websites($project);
if (!$add_websites)
{
    show_note($project);
}

$get_list = get_list($project);
if (!$get_list)
{
    show_note($project);
}

$delete_website = delete_website($project);
if (!$delete_website)
{
    show_note($project);
}

//Wait 8:30 minutes before running status checks
//sleep(60 * 8.5); 
sleep(15);

$get_websites_status = get_websites_status($project);
if (!$get_websites_status)
{
    show_note($project);
}

$get_history = get_history($project);
if (!$get_history)
{
    show_note($project);
}

//Wait 2 more minutes, so mail has time to arrive
//sleep(60 * 2);

$verif_mails = verif_mails($project);
if (!$verif_mails)
{
    show_note($project);
}
show_note($project);
