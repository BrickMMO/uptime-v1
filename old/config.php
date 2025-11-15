<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'brickmmo_uptime');
define('DB_USER', 'root');
define('DB_PASS', 'root');

define('SITE_URL', 'http://localhost:8888');
define('ADMIN_EMAIL', 'admin@brickmmo.com');
define('CHECK_INTERVAL', 300);
define('TIMEOUT', 30);

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("set names utf8");
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

session_start();

date_default_timezone_set('America/Toronto');

error_reporting(E_ALL);
ini_set('display_errors', 1);

