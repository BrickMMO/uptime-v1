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
            'Bricksum' => 'http://bricksum.local.brickmmo.com/',
            'Colours' => 'http://colours.local.brickmmo.com/',
            'Conversions' => 'http://conversions.local.brickmmo.com/',
            'Events' => 'http://events.local.brickmmo.com/',
            'List' => 'http://list.local.brickmmo.com/',
            'Parts' => 'http://parts.local.brickmmo.com/',
            'Placekit' => 'http://placekit.local.brickmmo.com/',
            'QR' => 'http://qr.local.brickmmo.com/',
            'Search' => 'http://search.local.brickmmo.com/',
            'SSO' => 'http://sso.local.brickmmo.com/',
            'Uptime' => 'http://uptime.local.brickmmo.com/',

            // 'Applications' => 'http://applications.local.brickmmo.com/',
            // 'GHitHub' => 'http://github.local.brickmmo.com/',
            // 'Media' => 'http://media.local.brickmmo.com/',
            // 'Stores' => 'http://stores.local.brickmmo.com/',
            // 'Stats' => 'http://stores.local.brickmmo.com/',
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
