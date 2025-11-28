<?php

/*
 * Dump data
 * 
 * This code ouputs all form, URL, session, and cookie data
 * if the ENV_DEBUG variable in the .env file is set to true.
 */
if(ENV_DEBUG)
{

if(ENV_LOCAL)
    {
        $links = array(
            'Bricksum' => 'http://bricksum.local.brickmmo.com:33/',
            'Colours' => 'http://colours.local.brickmmo.com:33/',
            'Conversions' => 'http://conversions.local.brickmmo.com:33/',
            'Events' => 'http://events.local.brickmmo.com:33/',
            'List' => 'http://list.local.brickmmo.com:33/',
            'Parts' => 'http://parts.local.brickmmo.com:33/',
            'Placekit' => 'http://placekit.local.brickmmo.com:33/',
            'QR' => 'http://qr.local.brickmmo.com:33/',
            'Search' => 'http://search.local.brickmmo.com:33/',
            'SSO' => 'http://sso.local.brickmmo.com:33/',
            'Uptime' => 'http://uptime.local.brickmmo.com:33/',

            // 'Applications' => 'http://applications.local.brickmmo.com:33/',
            // 'GHitHub' => 'http://github.local.brickmmo.com:33/',
            // 'Media' => 'http://media.local.brickmmo.com:33/',
            // 'Stores' => 'http://stores.local.brickmmo.com:33/',
            // 'Stats' => 'http://stores.local.brickmmo.com:33/',
        );


        echo '<ul>';
        foreach($links as $name => $url)
        {
            echo '<li><a href="'.$url.'">'.$name.'</a></li>';
        }
        echo '</ul>';
    }
    
    debug_pre($_GET);
    debug_pre($_POST);
    debug_pre($_SESSION);
    debug_pre($_COOKIE);
    // debug_pre(get_defined_constants());

}
