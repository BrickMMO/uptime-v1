<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BrickMMO Uptime Monitor</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
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
    </style>
</head>
<body class="w3-light-grey">

<script>
if (typeof window.updateAssetInfo !== 'function') {
    window.updateAssetInfo = function() {
        try {
            const select = document.getElementById('assetSelect');
            const checkButton = document.getElementById('checkButton');
            if (select && select.value) {
                if (checkButton) checkButton.disabled = false;
                window.currentAssetId = select.value;
            } else {
                if (checkButton) checkButton.disabled = true;
                window.currentAssetId = null;
            }
        } catch (e) {
        }
    };
}
</script>
<style>
    .topbar { background:#fff; border-bottom:1px solid #f0f0f0; padding:0; }
    .topbar .inner { width:100%; max-width:none; box-sizing:border-box; display:flex; align-items:center; justify-content:center; padding:12px; }
    .topbar .nav-links a { color:#f06d21; margin-left:18px; text-decoration:none; font-weight:700; }
    .topbar .nav-links a:hover { color:#d85b17; }
    @media (max-width:640px) { .topbar .inner { padding:8px 12px; } .topbar .nav-links a { margin-left:10px; } }
</style>

<div class="topbar">
    <div class="inner">
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="#">About</a>
            <?php if (isset($_SESSION['admin_logged_in'])): ?>
                <a href="admin/">Admin</a>
            <?php else: ?>
                <a href="admin/login.php">Admin</a>
            <?php endif; ?>
        </div>
    </div>
</div>
