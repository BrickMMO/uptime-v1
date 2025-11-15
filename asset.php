<?php
require_once 'config.php';
require_once 'monitor.php';

$asset_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$asset_id) {
    header('Location: index.php');
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
    header('Location: index.php');
    exit;
}

include 'includes/header.php';

$uptime_24h = $monitor->getAssetUptime($asset_id, 24);
$recent_checks = $monitor->getRecentChecks($asset_id, 100);
$page_errors = $monitor->getPageErrors($asset_id, 24);
?>

<div class="w3-container w3-margin-top">
    <div class="w3-card w3-white w3-padding">
        <h3><i class="fas fa-heartbeat w3-text-red"></i> Current Status</h3>
        
        <?php if (!empty($recent_checks)): 
            $latest = $recent_checks[0];
            $latest_status = isset($latest['status']) ? $latest['status'] : 'unknown';
            $latest_response_time = isset($latest['response_time']) && $latest['response_time'] !== null ? round($latest['response_time'], 2) : null;
            $latest_status_code = isset($latest['status_code']) ? $latest['status_code'] : null;
            $latest_checked_at = isset($latest['checked_at']) ? $latest['checked_at'] : null;
        ?>
        <div class="w3-row-padding">
            <div class="w3-col l3 m6 s12">
                <div class="w3-center w3-padding">
                    <i class="fas fa-<?= $latest_status === 'up' ? 'check-circle' : ($latest_status === 'down' ? 'times-circle' : 'exclamation-triangle') ?> 
                              status-<?= htmlspecialchars($latest_status) ?> w3-jumbo"></i>
                    <h4 class="status-<?= htmlspecialchars($latest_status) ?>"><?= strtoupper(htmlspecialchars($latest_status)) ?></h4>
                    <p>Current Status</p>
                </div>
            </div>
            
            <div class="w3-col l3 m6 s12">
                <div class="w3-center w3-padding">
                    <i class="fas fa-clock w3-text-orange w3-jumbo"></i>
                    <h4><?= $latest_response_time !== null ? $latest_response_time . 'ms' : 'N/A' ?></h4>
                    <p>Response Time</p>
                </div>
            </div>
            
            <div class="w3-col l3 m6 s12">
                <div class="w3-center w3-padding">
                    <i class="fas fa-code w3-text-blue w3-jumbo"></i>
                    <h4><?= $latest_status_code !== null && $latest_status_code !== '' ? htmlspecialchars($latest_status_code) : 'N/A' ?></h4>
                    <p>HTTP Status</p>
                </div>
            </div>
            
            <div class="w3-col l3 m6 s12">
                <div class="w3-center w3-padding">
                    <i class="fas fa-sync w3-text-grey w3-jumbo"></i>
                    <h4 data-timestamp="<?= $latest_checked_at ? strtotime($latest_checked_at) : '' ?>">
                        <?= $latest_checked_at ? date('H:i', strtotime($latest_checked_at)) : 'N/A' ?>
                    </h4>
                    <p>Last Check</p>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="w3-panel w3-yellow w3-round">
            <p><i class="fas fa-exclamation-triangle"></i> No monitoring data available yet.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($page_errors)): ?>
<div class="w3-container w3-margin-top">
    <div class="w3-card w3-white w3-padding">
        <h3><i class="fas fa-exclamation-triangle w3-text-red"></i> Recent Page Errors (24h)</h3>
        
        <div class="w3-responsive">
            <table class="w3-table w3-striped w3-bordered">
                <thead>
                    <tr class="w3-light-grey">
                        <th>Time</th>
                        <th>Error Type</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($page_errors as $error): ?>
                    <tr>
                        <td><?= date('M j, H:i', strtotime($error['occurred_at'])) ?></td>
                        <td><span class="w3-tag w3-red w3-round"><?= htmlspecialchars($error['error_type']) ?></span></td>
                        <td><?= htmlspecialchars($error['error_message']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="w3-container w3-margin-top">
    <div class="w3-card w3-white w3-padding">
        <h3><i class="fas fa-history w3-text-blue"></i> Recent Checks</h3>
        
        <div class="w3-responsive">
            <table class="w3-table w3-striped w3-bordered">
                <thead>
                    <tr class="w3-light-grey">
                        <th>Time</th>
                        <th>Status</th>
                        <th>Response Time</th>
                        <th>HTTP Code</th>
                        <th>Error</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($recent_checks, 0, 50) as $check): 
                        $c_status = isset($check['status']) ? $check['status'] : 'unknown';
                        $c_response = isset($check['response_time']) && $check['response_time'] !== null ? round($check['response_time'], 2) . 'ms' : 'N/A';
                        $c_status_code = isset($check['status_code']) ? $check['status_code'] : 'N/A';
                        $c_error_msg = isset($check['error_message']) ? $check['error_message'] : '';
                    ?>
                    <tr>
                        <td><?= isset($check['checked_at']) ? date('M j, H:i:s', strtotime($check['checked_at'])) : 'N/A' ?></td>
                        <td>
                            <span class="w3-tag w3-round w3-<?= $c_status === 'up' ? 'green' : ($c_status === 'down' ? 'red' : 'orange') ?>">
                                <i class="fas fa-<?= $c_status === 'up' ? 'check' : ($c_status === 'down' ? 'times' : 'exclamation') ?>"></i>
                                <?= strtoupper(htmlspecialchars($c_status)) ?>
                            </span>
                        </td>
                        <td><?= $c_response ?></td>
                        <td><?= htmlspecialchars($c_status_code) ?: 'N/A' ?></td>
                        <td class="w3-text-red"><?= $c_error_msg ? htmlspecialchars($c_error_msg) : '' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
