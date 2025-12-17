<?php

/*
 * Start the session
 */
session_set_cookie_params([
    'lifetime' => 0,
    'domain' => ENV_LOCAL ? '.local.brickmmo.com' : '.brickmmo.com',
    'path' => '/',
    'secure' => ENV_HTTPS ? true : false,
    // 'httponly' => true,
    'samesite' => ENV_HTTPS ? 'None' : 'Lax',
]);

session_start();