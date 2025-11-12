<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'run_check') {
        require_once '../monitor.php';
        try {
            $monitor->checkAllAssets();
            $message = 'Manual uptime check completed successfully!';
        } catch (Exception $e) {
            $error = 'Error running check: ' . $e->getMessage();
        }
    } elseif ($action === 'cleanup') {
        $deleted_checks = 0;
        $deleted_errors = 0;
        
        try {
            $stmt = $pdo->prepare("DELETE FROM uptime_checks WHERE checked_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stmt->execute();
            $deleted_checks = $stmt->rowCount();
        } catch (PDOException $e) {
        }
        
        try {
            $stmt = $pdo->prepare("DELETE FROM page_errors WHERE occurred_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stmt->execute();
            $deleted_errors = $stmt->rowCount();
        } catch (PDOException $e) {
        }
        
        $message = "Cleanup completed: Removed $deleted_checks old checks and $deleted_errors old errors.";
    }
}

include 'includes/header.php';

$stmt = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM assets WHERE status = 'active') as active_assets,
        (SELECT COUNT(*) FROM uptime_checks) as total_checks,
        (SELECT COUNT(*) FROM uptime_checks WHERE checked_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) as checks_24h,
        (SELECT COUNT(*) FROM page_errors WHERE occurred_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) as errors_24h
");
$stmt->execute();
$system_stats = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT 
        table_name,
        ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'size_mb',
        table_rows
    FROM information_schema.tables 
    WHERE table_schema = ? 
    AND table_name IN ('assets', 'uptime_checks', 'page_errors', 'admins')
    ORDER BY (data_length + index_length) DESC
");
$stmt->execute([DB_NAME]);
$table_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT MAX(checked_at) as last_check FROM uptime_checks");
$stmt->execute();
$last_check = $stmt->fetch(PDO::FETCH_ASSOC)['last_check'];
?>

<div class="w3-container w3-margin-top">
    <h2><i class="fas fa-cog w3-text-blue"></i> System Settings</h2>
    
    <?php if ($message): ?>
    <div class="w3-panel w3-green w3-round w3-margin-top">
        <p><i class="fas fa-check"></i> <?= htmlspecialchars($message) ?></p>
    </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="w3-panel w3-red w3-round w3-margin-top">
        <p><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></p>
    </div>
    <?php endif; ?>
    
    <div class="w3-card w3-white w3-padding w3-margin-top">
        <h3><i class="fas fa-info-circle w3-text-blue"></i> System Information</h3>
        
        <div class="w3-row-padding">
            <div class="w3-col l6 m12 s12">
                <table class="w3-table">
                    <tr>
                        <td><strong>PHP Version:</strong></td>
                        <td><?= PHP_VERSION ?></td>
                    </tr>
                    <tr>
                        <td><strong>Database:</strong></td>
                        <td><?= DB_NAME ?> @ <?= DB_HOST ?></td>
                    </tr>
                    <tr>
                        <td><strong>Check Interval:</strong></td>
                        <td><?= CHECK_INTERVAL ?> seconds (<?= CHECK_INTERVAL / 60 ?> minutes)</td>
                    </tr>
                    <tr>
                        <td><strong>Request Timeout:</strong></td>
                        <td><?= TIMEOUT ?> seconds</td>
                    </tr>
                    <tr>
                        <td><strong>Timezone:</strong></td>
                        <td><?= date_default_timezone_get() ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="w3-col l6 m12 s12">
                <table class="w3-table">
                    <tr>
                        <td><strong>Active Assets:</strong></td>
                        <td><?= $system_stats['active_assets'] ?></td>
                    </tr>
                    <tr>
                        <td><strong>Total Checks:</strong></td>
                        <td><?= number_format($system_stats['total_checks']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Checks (24h):</strong></td>
                        <td><?= number_format($system_stats['checks_24h']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Errors (24h):</strong></td>
                        <td><?= number_format($system_stats['errors_24h']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Last Check:</strong></td>
                        <td><?= $last_check ? date('Y-m-d H:i:s', strtotime($last_check)) : 'Never' ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="w3-card w3-white w3-padding w3-margin-top">
        <h3><i class="fas fa-database w3-text-green"></i> Database Statistics</h3>
        
        <div class="w3-responsive">
            <table class="w3-table w3-striped w3-bordered">
                <thead>
                    <tr class="w3-light-grey">
                        <th>Table</th>
                        <th>Rows</th>
                        <th>Size (MB)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($table_stats as $table): ?>
                    <tr>
                        <td><code><?= $table['table_name'] ?></code></td>
                        <td><?= number_format($table['table_rows']) ?></td>
                        <td><?= $table['size_mb'] ?> MB</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="w3-card w3-white w3-padding w3-margin-top">
        <h3><i class="fas fa-tools w3-text-orange"></i> System Actions</h3>
        
        <div class="w3-row-padding">
            <div class="w3-col l6 m12 s12">
                <div class="w3-panel w3-leftbar w3-light-blue w3-border-blue">
                    <h4><i class="fas fa-play"></i> Manual Check</h4>
                    <p>Run a manual uptime check for all active assets immediately.</p>
                    <form method="POST">
                        <input type="hidden" name="action" value="run_check">
                        <button type="submit" class="w3-button w3-blue w3-round">
                            <i class="fas fa-play"></i> Run Check Now
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="w3-col l6 m12 s12">
                <div class="w3-panel w3-leftbar w3-light-orange w3-border-orange">
                    <h4><i class="fas fa-broom"></i> Data Cleanup</h4>
                    <p>Remove monitoring data older than 30 days to free up space.</p>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete old monitoring data?')">
                        <input type="hidden" name="action" value="cleanup">
                        <button type="submit" class="w3-button w3-orange w3-round">
                            <i class="fas fa-broom"></i> Clean Up Old Data
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
