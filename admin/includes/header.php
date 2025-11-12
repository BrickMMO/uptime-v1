<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - BrickMMO Uptime</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    <style>
        body { margin:0; font-family: 'Roboto', Arial, sans-serif; }
        .status-up { color: #4CAF50; }
        .status-down { color: #f44336; }
        .status-error { color: #ff9800; }
        .uptime-card { transition: all 0.3s; }
        .uptime-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .chart-container { height: 200px; }
        .status-indicator { width: 12px; height: 12px; border-radius: 50%; display: inline-block; margin-right: 8px; }
        .status-indicator.up { background-color: #4CAF50; }
        .status-indicator.down { background-color: #f44336; }
        .status-indicator.error { background-color: #ff9800; }
        
        .topbar { background:#fff; border-bottom:1px solid #f0f0f0; padding:0; }
        .topbar .inner { width:100%; max-width:none; box-sizing:border-box; display:flex; align-items:center; justify-content:center; padding:12px; }
        .topbar .nav-links a { color:#f06d21; margin-left:18px; text-decoration:none; font-weight:700; }
        .topbar .nav-links a:hover { color:#d85b17; }
        @media (max-width:640px) { .topbar .inner { padding:12px; } .topbar .nav-links a { margin-left:10px; } }
    </style>
</head>
<body class="w3-light-grey">

<div class="topbar">
    <div class="inner">
        <div class="nav-links">
            <a href="../index.php">Home</a>
            <a href="#">About</a>
            <a href="index.php">Admin</a>
        </div>
    </div>
</div>

<style>
    .admin-sidebar { z-index:3; width:250px; background: white; }
    .admin-sidebar .sidebar-header { padding: 20px 16px; border-bottom: 1px solid #f0f0f0; }
    .admin-sidebar .sidebar-header h4 { margin: 0; color: #333; font-weight: 700; }
    .admin-sidebar .sidebar-header .user-info { color: #666; font-size: 14px; margin-top: 8px; }
    .admin-sidebar .w3-bar-item { padding: 12px 16px; border-left: 3px solid transparent; }
    .admin-sidebar .w3-bar-item:hover { background-color: #f5f5f5; }
    .admin-sidebar .w3-bar-item.active { border-left-color: #f06d21; background-color: #fff3ed; color: #f06d21; }
    .admin-sidebar .w3-bar-item i { width: 20px; margin-right: 10px; }
    .admin-main-content { margin-left:250px; padding: 20px; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; } }
</style>

<nav class="w3-sidebar w3-collapse w3-animate-left admin-sidebar" id="mySidebar">
    <div class="sidebar-header">
        <h4><i class="fas fa-cogs" style="color:#f06d21;"></i> Admin Panel</h4>
        <div class="user-info">
            <i class="fas fa-user"></i> <?= $_SESSION['admin_username'] ?? 'Admin' ?>
        </div>
    </div>
    <div class="w3-bar-block">
        <a href="index.php" class="w3-bar-item w3-button <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i> Overview
        </a>
        <a href="assets.php" class="w3-bar-item w3-button <?= basename($_SERVER['PHP_SELF']) == 'assets.php' ? 'active' : '' ?>">
            <i class="fas fa-globe"></i> Manage Assets
        </a>
        <a href="reports.php" class="w3-bar-item w3-button <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-line"></i> Reports
        </a>
        <a href="settings.php" class="w3-bar-item w3-button <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">
            <i class="fas fa-cog"></i> Settings
        </a>
        <hr style="margin: 10px 0; border-color: #f0f0f0;">
        <a href="logout.php" class="w3-bar-item w3-button">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</nav>

<div class="w3-overlay w3-hide-large w3-animate-opacity" onclick="w3_close()" style="cursor:pointer" title="close side menu" id="myOverlay"></div>

<div class="admin-main-content">
    <header class="w3-container" style="padding-left:0;">
        <a class="w3-button w3-hide-large w3-hover-text-grey" href="javascript:void(0)" onclick="w3_open()" style="padding:8px 16px;">
            <i class="fas fa-bars"></i>
        </a>
    </header>
