<?php

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
    $url['query'] = http_build_query($params);
    $url = unparse_url($url);

    #make query
    $url = $url;

    // Initializing curl
    $ch = curl_init($url);

    // Configuring curl options
    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array('Content-type: application/json') ,
    );


    if ($post)
    {
        $options['CURLOPT_POST'] = count($post);
        $options['CURLOPT_POSTFIELDS'] = http_build_query($post);
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

$websites = [];

for ($i = 1; $i <= 10; $i++)
{
    $port = 8000 + $i;
    $websites[$i] = [
        'url' => 'http://127.0.0.1:' . $port . '/httpstatus/api/',
    ];
}

foreach ($websites as $key => $website)
{
    $json = query($website['url']);
    $websites[$key]['list'] = $json['list'] ?? false;
}

var_dump($websites);
