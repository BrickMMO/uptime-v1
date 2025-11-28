<?php

/**
 * All URLs that do not match an existing file are routed to this page via the 
 * .htaccss file. This files splits the URL into a variety of components to 
 * determine which PHP file to execute and which parts of the URL are variables. 
 * 
 * For example:
 * http://console.local.brickmmo.com:33/media/tags/edit/1
 * Will route to the /console/media.tags.php file with a variable named edit
 * with a value of 1.
 */

/**
 * Load libraries through composer.
 */
require __DIR__ . '/../vendor/autoload.php';

/**
 * Include database connecton, session initialiation, and function
 * files.
 */
include('../includes/connect.php');
include('../includes/session.php');
include('../includes/config.php');
include('../functions/functions.php');

/**
 * Fetch user if applicable.
 */ 
if(isset($_SESSION['user'])) $_user = user_fetch($_SESSION['user']['id']);
else $_user = false;

/**
 * Get domain.
 */
if(is_numeric(strpos($_SERVER['HTTP_HOST'], 'uptime.'))) $domain = 'uptime';
else
{

    include('404.php');
    exit;
    
}


/**
 * Convert standard format URL parameters to slashes.
 */ 
if(strpos($_SERVER['REQUEST_URI'], '?'))
{

    $url = $_SERVER['REQUEST_URI'];
    $url = explode('?', $url);
    $url[1] = str_replace(array('/', '%2F'), urlencode('-SLASH-'), $url[1]);
    $url[1] = str_replace(array('?','=', '&'), '/', $url[1]);
    $url = implode('/', $url);
    debug_pre($url);
    header_redirect($url);

}

/**
 * Split URL infor array.
 */ 
$parts = array_filter(explode("/", trim($_SERVER['REQUEST_URI'], "/")));

/**
 * If there are no parts, redirect to home page.
 */
if(!count($parts))
{

    header_redirect(ENV_DOMAIN.'/list');

}

/**
 * If the request is an ajax request. 
 */
if($parts[0] == 'ajax')
{

    define('PAGE_TYPE', 'ajax');
    array_shift($parts);
    $folder = 'ajax/';

}

/**
 * If the request is a action request. 
 */
elseif($parts[0] == 'action')
{

    define('PAGE_TYPE', 'action');
    array_shift($parts);
    $folder = 'action/';

}

/**
 * If the request is an API request. 
 */
elseif($parts[0] == 'api')
{

    define('PAGE_TYPE', 'api');
    array_shift($parts);
    $folder = 'api/';

    /*
    header("Content-type: application/json; charset=utf-8");
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header("Access-Control-Allow-Headers: X-Requested-With");
    */

    /*
    header("Access-Control-Allow-Origin: https://assets.brickmmo.com");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
    */

    $origin = $_SERVER['HTTP_ORIGIN'] ?? false;

    if($origin && preg_match('/\.?brickmmo\.com$/', parse_url($origin, PHP_URL_HOST)))
    {
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
    }

    header('Content-Type: application/json; charset=utf-8');

}

/**
 * If the request is a standard web request. 
 */
else
{

    define('PAGE_TYPE', 'web');
    $folder = $domain;
    
}

/**
 * Parse URL for possible filenames and check if file exists. 
 */
$file = '';
$final_file = '';

foreach($parts as $part)
{
    
    $file = str_replace('php', '', $file);
    $file .= array_shift($parts).'.php';

    if(file_exists('../'.$folder.'/'.$file)) 
    {
        $final_file = $file;
        $final_parts = $parts;
    }

}

if($final_file) define('PAGE_FILE', $final_file);

/**
 * If URL does not result in an existing file. 
 */
if(!defined('PAGE_FILE'))
{

    include('404.php');
    exit;

}

/**
 * Parse remaining URL data into a $_GET array. 
 */

 /**
  * If there is only one part, the value is placed into the $_GET array with the
  * key set to "key". 
  */
if(count($final_parts) == 1)
{

    $_GET['key'] = array_shift($final_parts);

}

/**
 * If there are an odd number of parts, the final part is placed into the $_GET array 
 * with the key set to "key". 
 */
elseif(count($final_parts) % 2 == 1)
{

    $_GET['key'] = array_pop($final_parts);
    /*
    while($next = array_shift($final_parts))
    {
        if($next) $_GET['key'] = $next;
    }
    */
    
}

/**
 * Remaining parts are placed into the $_GET array using alternating parts as keys
 * and values.
 */
for($i = 0; $i < count($final_parts); $i += 2)
{

    /**
     * Slashed return from the Google API were breaking the .htaccess, so slashes in 
     * the URL paramaters are replaced with "-SLASH-" and switched back to slashes 
     * below. There must be a better solution to this, but this works for now. 
     */
    $_GET[$final_parts[$i]] = isset($final_parts[$i+1]) ? 
        urldecode(str_replace('-SLASH-', '/', $final_parts[$i+1])) : 
        true;

}

/**
 * If the request is an ajax request. 
 */
if(PAGE_TYPE == 'ajax') 
{

    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header("Access-Control-Allow-Headers: X-Requested-With");

    $_POST = json_decode(file_get_contents('php://input'), true);
    include('../ajax/'.PAGE_FILE);
    echo json_encode($data);
    exit;
    
}

/**
 * If the request is an API request. 
 */
elseif(PAGE_TYPE == 'api') 
{

    include('../api/'.PAGE_FILE);
    echo json_encode($data);
    exit;

}

/**
 * If the request is an action request. 
 */
elseif(PAGE_TYPE == 'action') 
{

    include('../action/'.PAGE_FILE);
    exit;

}

/**
 * If the request is a standard web request. 
 */
else
{
    
    include('../'.$folder.'/'.PAGE_FILE);

}

