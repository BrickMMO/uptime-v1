<?php

function fetch_contents($url)
{

    $url = string_url_local($url);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $output = curl_exec($ch);
    curl_close($ch);
    
    return $output;

}

function fetch_json($url)
{

    $contents = fetch_contents($url);
    return json_decode($contents, true);

}
