<?php
require_once 'config.php';
require_once 'monitor.php';

echo "Starting uptime check at " . date('Y-m-d H:i:s') . "\n";

try {
    $monitor->checkAllAssets();
    echo "Uptime check completed successfully.\n";
    
    try {
        $stmt = $pdo->prepare("DELETE FROM checks WHERE checked_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute();
        $deleted_checks = $stmt->rowCount();
    } catch (PDOException $e) {
        $deleted_checks = 0;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM page_errors WHERE occurred_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute();
        $deleted_errors = $stmt->rowCount();
    } catch (PDOException $e) {
        $deleted_errors = 0;
    }
    
    if ($deleted_checks > 0 || $deleted_errors > 0) {
        echo "Cleaned up old data: $deleted_checks checks, $deleted_errors errors\n";
    }
    
} catch (Exception $e) {
    echo "Error during uptime check: " . $e->getMessage() . "\n";
    error_log("Uptime Monitor Error: " . $e->getMessage());
}

echo "Check completed at " . date('Y-m-d H:i:s') . "\n";
?>
