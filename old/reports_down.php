<?php
require_once 'config.php';
require_once 'monitor.php';

$asset_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$asset_id) {
    header('Location: select.php');
    exit;
}

$asset = false;
try {
    $stmt = $pdo->prepare("SELECT * FROM assets WHERE id = ? LIMIT 1");
    $stmt->execute([$asset_id]);
    $asset = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $asset = false;
}

if (!$asset) {
    header('Location: select.php');
    exit;
}

$check = $monitor->checkAsset($asset);

$uptime_24h = $monitor->getAssetUptime($asset_id, 24);

include 'includes/header.php';
?>

<?php
$status = $check['status'] ?? 'unknown';
$response_time = isset($check['response_time']) ? htmlspecialchars(round($check['response_time']) . ' ms') : 'N/A';
$http_status = htmlspecialchars($check['http_code'] ?? ($check['status_code'] ?? 'N/A'));
$uptime_pct = htmlspecialchars($uptime_24h['uptime_percentage'] ?? 0);

$last_downtime = 'N/A';
try {
    $recent = $monitor->getRecentChecks($asset_id, 500);
    foreach ($recent as $rc) {
        if (isset($rc['status']) && $rc['status'] === 0) {
            $ts = isset($rc['checked_at']) ? strtotime($rc['checked_at']) : null;
            if ($ts) {
                $diff = time() - $ts;
                if ($diff < 60) $last_downtime = 'Just now';
                elseif ($diff < 3600) $last_downtime = floor($diff/60) . ' minutes ago';
                elseif ($diff < 86400) $last_downtime = floor($diff/3600) . ' hours ago';
                else $last_downtime = floor($diff/86400) . ' days ago';
            } else {
                $last_downtime = 'Unknown';
            }
            break;
        }
    }
} catch (Exception $e) {
    $last_downtime = 'N/A';
}

$last_checked = date('F j, Y \a\t g:i A');
?>

<style>
    .report-hero { background: #fff3f3; padding: 18px; border-radius: 8px; }
    .status-badge { display:flex; align-items:center; gap:12px; }
    .status-badge .down { color: #f44336; font-weight:700; font-size:30px; }
    .status-badge .up { color: #4CAF50; font-weight:700; font-size:30px; }
    .report-title { font-size:48px; font-weight:700; color:#666; }
    .metric-card { border:2px solid #f44336; border-radius:12px; padding:18px; text-align:center; background:white; }
    .metric-card .label { color:#666; }
    .metric-card .value { color:#f44336; font-size:32px; font-weight:800; }
</style>

<div class="w3-container w3-margin-top">
    <div class="w3-row report-hero">
        <div class="w3-col s2 status-badge">
            <?php if ($status === 0): ?>
                <div class="down"><i class="fas fa-times-circle"></i> DOWN</div>
            <?php else: ?>
                <div class="up" style="color:#4CAF50;"><i class="fas fa-check-circle"></i> UP</div>
            <?php endif; ?>
        </div>
        <div class="w3-col s8">
            <div class="report-title"><?= htmlspecialchars($asset['name'] ?: 'Display Asset') ?></div>
            <div class="w3-text-red">Last checked: <?= $last_checked ?></div>
        </div>
        <div class="w3-col s2 w3-right-align">
            <a href="select.php" class="w3-button w3-light-grey w3-round">&larr; Back</a>
        </div>
    </div>
</div>

<div class="w3-container w3-margin-top">
    <div class="w3-row-padding">
        <div class="w3-col l12 m12 s12">
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:18px;">
                <div class="metric-card">
                    <div class="label">Response Time</div>
                    <div class="value"><?= htmlspecialchars($response_time) ?></div>
                </div>
                <div class="metric-card">
                    <div class="label">HTTP Status</div>
                    <div class="value"><?= $http_status ?></div>
                </div>
                <div class="metric-card">
                    <div class="label">UPTIME</div>
                    <div class="value"><?= htmlspecialchars($uptime_pct) ?>%</div>
                </div>
                <div class="metric-card">
                    <div class="label">Last Downtime</div>
                    <div class="value"><?= htmlspecialchars($last_downtime) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
