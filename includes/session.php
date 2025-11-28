<?php

/*
 * Start the session
 */
session_set_cookie_params([
    'domain' => ENV_LOCAL ? '.local.brickmmo.com:33' : '.brickmmo.com',
    'path' => '/',
    'secure' => ENV_HTTPS ? true : false,
    // 'httponly' => true,
    'samesite' => ENV_HTTPS ? 'None' : 'Lax',
]);

session_start();
