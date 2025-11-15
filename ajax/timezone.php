<?php

$_SESSION['timezone']['offset'] = $_GET['key'][0];
$_SESSION['timezone']['timezone'] = $_GET['key'][1].'/'.$_GET['key'][2];

$data = array(
    'message' => 'Timesone has been set.', 
    'error' => false
);