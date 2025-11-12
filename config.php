<?php
define('DB_HOST', 'sql105.infinityfree.com');
define('DB_NAME', 'if0_40325904_brickmmo_uptime');
define('DB_USER', 'if0_40325904');
define('DB_PASS', '3UYR9uOz8yPlzUb');

define('SITE_URL', 'https://brickmmo-uptime.infinityfreeapp.com');
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
?>
