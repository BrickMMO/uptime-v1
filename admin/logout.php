<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

session_destroy();
header('Location: ../index.php');
exit;
?>
