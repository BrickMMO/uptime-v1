<?php
require_once 'config.php';
require_once 'monitor.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json', true, 405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

ob_start();
try {
    $asset_id = isset($_POST['asset_id']) ? (int)$_POST['asset_id'] : 0;
    if (!$asset_id) {
        ob_end_clean();
        header('Content-Type: application/json', true, 400);
        echo json_encode(['success' => false, 'error' => 'Missing asset_id']);
        exit;
    }

    $asset = false;
    try {
        $stmt = $pdo->prepare("SELECT * FROM assets WHERE asset_id = ? AND status = 1 LIMIT 1");
        $stmt->execute([$asset_id]);
        $asset = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $asset = false;
    }
    if (!$asset) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM assets WHERE id = ? AND status = 1 LIMIT 1");
            $stmt->execute([$asset_id]);
            $asset = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $asset = false;
        }
    }

    if (!$asset) {
        ob_end_clean();
        header('Content-Type: application/json', true, 404);
        echo json_encode(['success' => false, 'error' => 'Asset not found']);
        exit;
    }

    $monitor = new UptimeMonitor($pdo, false);
    $result = $monitor->checkAsset($asset);
    $recent_checks = $monitor->getRecentChecks($asset_id, 50);

    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'asset' => $asset,
        'current_check' => $result,
        'recent_checks' => $recent_checks,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
} catch (Throwable $e) {
    if (ob_get_length()) ob_end_clean();
    header('Content-Type: application/json', true, 500);
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    exit;
}

?>