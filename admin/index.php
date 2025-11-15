<?php
require_once '../config.php';
require_once '../monitor.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

include 'includes/header.php';

try {
    $sql = "SELECT COUNT(DISTINCT a.asset_id) as total_assets, COUNT(c.check_id) as total_checks_24h, SUM(CASE WHEN c.status = 'up' THEN 1 ELSE 0 END) as up_checks_24h, AVG(c.response_time) as avg_response_time_24h FROM assets a LEFT JOIN checks c ON a.asset_id = c.asset_id WHERE a.status = 'active' AND c.checked_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $sql = "SELECT COUNT(DISTINCT a.id) as total_assets, COUNT(uc.id) as total_checks_24h, SUM(CASE WHEN uc.status = 'up' THEN 1 ELSE 0 END) as up_checks_24h, AVG(uc.response_time) as avg_response_time_24h FROM assets a LEFT JOIN uptime_checks uc ON a.id = uc.asset_id WHERE a.status = 'active' AND uc.checked_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
}

try {
    $sql = "SELECT a.name, c.status, c.error_message, c.checked_at FROM checks c JOIN assets a ON c.asset_id = a.asset_id WHERE c.status != 'up' AND c.checked_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY c.checked_at DESC LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $recent_issues = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $sql = "SELECT a.name, uc.status, uc.error_message, uc.checked_at FROM uptime_checks uc JOIN assets a ON uc.asset_id = a.id WHERE uc.status != 'up' AND uc.checked_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY uc.checked_at DESC LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $recent_issues = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

try {
    $sql = "SELECT a.*, c.status as latest_status, c.response_time as latest_response_time, c.checked_at as last_check 
            FROM assets a 
            LEFT JOIN checks c ON a.asset_id = c.asset_id 
            WHERE a.status = 'active' 
            AND (c.checked_at = (SELECT MAX(checked_at) FROM checks WHERE asset_id = a.asset_id) OR c.checked_at IS NULL)
            ORDER BY a.name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $assets_status = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $sql = "SELECT a.*, uc.status as latest_status, uc.response_time as latest_response_time, uc.checked_at as last_check 
            FROM assets a 
            LEFT JOIN uptime_checks uc ON a.id = uc.asset_id 
            WHERE a.status = 'active' 
            AND (uc.checked_at = (SELECT MAX(checked_at) FROM uptime_checks WHERE asset_id = a.id) OR uc.checked_at IS NULL)
            ORDER BY a.name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $assets_status = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

foreach ($assets_status as &$a) {
    if (empty($a['id']) && !empty($a['asset_id'])) {
        $a['id'] = $a['asset_id'];
    }
}
unset($a);

$assets_status = array_slice($assets_status, 0, 4);

$overall_uptime = $stats['total_checks_24h'] > 0 ? ($stats['up_checks_24h'] / $stats['total_checks_24h']) * 100 : 0;
?>

<!-- Updated: <?= time() ?> -->
<style>
    .stat-card {
        background: white !important;
        border-left: 4px solid #f06d21 !important;
        padding: 20px !important;
        margin-bottom: 16px !important;
        border-radius: 4px !important;
    }
    .stat-card i {
        color: #f06d21 !important;
        font-size: 48px !important;
    }
    .stat-card h3 {
        font-weight: 700 !important;
        color: #333 !important;
        margin: 10px 0 !important;
        font-size: 36px !important;
    }
    .stat-card p {
        color: #6b6f73 !important;
        margin: 0 !important;
        font-size: 14px !important;
    }
</style>

<div class="w3-container w3-margin-top">
    <h2 style="color: #333;"><i class="fas fa-chart-bar" style="color: #f06d21;"></i> Admin Overview</h2>
    
    <div class="w3-row-padding w3-margin-top">
        <div class="w3-col l3 m6 s12">
            <div class="w3-card stat-card w3-center">
                <i class="fas fa-globe"></i>
                <h3><?= $stats['total_assets'] ?></h3>
                <p>Monitored Assets</p>
            </div>
        </div>
        
        <div class="w3-col l3 m6 s12">
            <div class="w3-card stat-card w3-center">
                <i class="fas fa-heartbeat"></i>
                <h3><?= round($overall_uptime, 1) ?>%</h3>
                <p>Overall Uptime (24h)</p>
            </div>
        </div>
        
        <div class="w3-col l3 m6 s12">
            <div class="w3-card stat-card w3-center">
                <i class="fas fa-clock"></i>
                <h3><?= round($stats['avg_response_time_24h'], 0) ?>ms</h3>
                <p>Avg Response Time</p>
            </div>
        </div>
        
        <div class="w3-col l3 m6 s12">
            <div class="w3-card stat-card w3-center">
                <i class="fas fa-exclamation-triangle"></i>
                <h3><?= count($recent_issues) ?></h3>
                <p>Issues (24h)</p>
            </div>
        </div>
    </div>
    
    <div class="w3-card w3-white w3-margin-top w3-padding">
        <h3 style="color: #333;"><i class="fas fa-bolt" style="color: #f06d21;"></i> Quick Actions</h3>
        <div class="w3-row-padding">
            <div class="w3-col l6 m12 s12">
                <a href="assets.php?action=add" class="w3-button w3-large w3-block w3-margin-bottom" style="background-color: #4CAF50; color: white;">
                    <i class="fas fa-plus"></i> Add New Asset
                </a>
            </div>
            <div class="w3-col l6 m12 s12">
                <a href="reports.php" class="w3-button w3-large w3-block w3-margin-bottom" style="background-color: #f06d21; color: white;">
                    <i class="fas fa-chart-line"></i> View Reports
                </a>
            </div>
        </div>
    </div>
    
    <div class="w3-card w3-white w3-margin-top w3-padding">
        <h3 style="color: #333;"><i class="fas fa-list" style="color: #f06d21;"></i> Assets Status</h3>
        <div class="w3-responsive">
            <table class="w3-table w3-striped w3-bordered">
                <thead>
                    <tr style="background-color: #f5f5f5;">
                        <th>Asset</th>
                        <th>Status</th>
                        <th>Response Time</th>
                        <th>Last Check</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assets_status as $asset): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($asset['name']) ?></strong><br>
                            <small class="w3-text-grey"><?= htmlspecialchars($asset['url']) ?></small>
                        </td>
                        <td>
                            <?php if ($asset['latest_status']): ?>
                            <span class="w3-tag w3-round w3-<?= $asset['latest_status'] === 'up' ? 'green' : ($asset['latest_status'] === 'down' ? 'red' : 'orange') ?>">
                                <i class="fas fa-<?= $asset['latest_status'] === 'up' ? 'check' : ($asset['latest_status'] === 'down' ? 'times' : 'exclamation') ?>"></i>
                                <?= strtoupper($asset['latest_status']) ?>
                            </span>
                            <?php else: ?>
                            <span class="w3-tag w3-grey w3-round">NO DATA</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $asset['latest_response_time'] ? round($asset['latest_response_time'], 2) . 'ms' : 'N/A' ?></td>
                        <td><?= $asset['last_check'] ? date('M j, H:i', strtotime($asset['last_check'])) : 'Never' ?></td>
                        <td>
                            <a href="../asset.php?id=<?= $asset['id'] ?>" class="w3-button w3-small w3-round" style="background-color: #f06d21; color: white;">
                                <i class="fas fa-chart-line"></i> View
                            </a>
                            <a href="assets.php?action=edit&id=<?= $asset['id'] ?>" class="w3-button w3-small w3-round" style="background-color: #ff9800; color: white;">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if (!empty($recent_issues)): ?>
    <div class="w3-card w3-white w3-margin-top w3-padding">
        <h3 style="color: #333;"><i class="fas fa-exclamation-triangle" style="color: #f44336;"></i> Recent Issues (24h)</h3>
        <div class="w3-responsive">
            <table class="w3-table w3-striped w3-bordered">
                <thead>
                    <tr style="background-color: #f5f5f5;">
                        <th>Time</th>
                        <th>Asset</th>
                        <th>Status</th>
                        <th>Error Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_issues as $issue): ?>
                    <tr>
                        <td><?= date('M j, H:i:s', strtotime($issue['checked_at'])) ?></td>
                        <td><?= htmlspecialchars($issue['name']) ?></td>
                        <td>
                            <span class="w3-tag w3-round w3-<?= $issue['status'] === 'down' ? 'red' : 'orange' ?>">
                                <i class="fas fa-<?= $issue['status'] === 'down' ? 'times' : 'exclamation' ?>"></i>
                                <?= strtoupper($issue['status']) ?>
                            </span>
                        </td>
                        <td class="w3-text-red"><?= $issue['error_message'] ? htmlspecialchars($issue['error_message']) : 'No specific error message' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
