<?php

function string_hash($length = 10)
{

    $length--;
    return rand(pow(10, $length), pow(10, $length + 1) - 1);

}

function string_random($length = 10)
{

    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';
    
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $string;

}

function string_split_name($name)
{

    $names = explode(' ', $name);

    $result['first'] = $names[0];
    $result['last'] = $names[count($names)-1];

    return $result;

}

function string_is_base64($string)
{

    if (base64_decode($string, true) !== false) return true;
    else return false;

}

function string_shorten($text, $limit = 100, $cut_at_space = false) 
{

    if (strlen($text) <= $limit) 
    {
        return $text;
    }

    $short = substr($text, 0, $limit);

    if ($cut_at_space) 
    {
        $last_space = strrpos($short, ' ');
        if ($last_space !== false) 
        {
            $short = substr($short, 0, $last_space);
        }
    }

    return rtrim($short) . '...';
    
}

function string_normalize_url($url)
{
    // Parse the URL
    $parsed = parse_url(trim($url));
    
    // If parsing failed, return original URL lowercased
    if ($parsed === false) {
        return strtolower(trim($url));
    }
    
    // Default to http if no scheme
    $scheme = isset($parsed['scheme']) ? strtolower($parsed['scheme']) : 'http';
    
    // Get host (domain)
    $host = isset($parsed['host']) ? strtolower($parsed['host']) : '';
    
    // Remove www. prefix for consistency
    $host = preg_replace('/^www\./', '', $host);
    
    // Get path and remove trailing slash
    $path = isset($parsed['path']) ? $parsed['path'] : '/';
    $path = rtrim($path, '/');
    if ($path === '') $path = '/';
    
    // Get port if specified
    $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
    
    // Get query string if present
    $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
    
    // Reconstruct normalized URL (always use https for comparison)
    return 'https://' . $host . $port . $path . $query;
}
