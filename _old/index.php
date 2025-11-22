<?php

require_once 'config.php';
require_once 'monitor.php';

if (isset($_POST['check_asset']) && isset($_POST['asset_id'])) {
    ob_start();
    try {
        $asset_id = (int)$_POST['asset_id'];

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
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Asset not found']);
            exit;
        }

        $result = $monitor->checkAsset($asset);

        $recent_checks = $monitor->getRecentChecks($asset_id, 5);

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
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

include 'includes/header.php';

?>

<style>
    html, body { overflow: hidden; height: 100%; }
    .hero { padding: 48px 0; min-height: calc(100vh - 140px); display:block; }
    .hero-title { font-size: clamp(56px, 8vw, 110px); font-weight: 900; margin: 0 0 6px 0; color: #333; }
    .hero-sub { color: #f06d21; font-weight: 700; margin-top: 6px; font-size: clamp(20px,3.6vw,36px); }
    .hero-lead { color: #6b6f73; max-width: 640px; font-size:16px; line-height:1.6; }
    .hero-cta { display:inline-block; border:2px solid #f06d21; color:#f06d21; font-weight:700; padding:10px 18px; border-radius:10px; background:transparent; font-size:16px; text-decoration:none; margin-top:20px; }
    .hero-image { max-width: 100%; height: auto; display:block; }
    @media (min-width:992px) {
        .hero-title { font-size: 72px; }
    }
</style>

<div class="w3-container hero">
    <div class="w3-row-padding w3-center-align">
        <div class="w3-col l7 m12 s12 w3-padding-48">
            <h1 class="hero-title">BrickMMO</h1>
            <div class="hero-sub">Uptime Project</div>
            <p class="hero-lead w3-margin-top">Monitor the health and performance of all BrickMMO websites in real-time. Track uptime, response times, and receive instant notifications when issues are detected. Keep your digital infrastructure running smoothly with comprehensive monitoring and detailed reports.</p>
            <p class="w3-text-orange">Uptime Check Uptime and Downtime</p>

            <p class="w3-margin-top">
                <a href="select.php" class="hero-cta" role="button">Get Started</a>
            </p>
        </div>

        <div class="w3-col l5 m12 s12 w3-padding-48 w3-hide-small w3-right-align">
            <img src="assets/lego.png" alt="BrickMMO characters" class="hero-image">
        </div>
    </div>
</div>



<?php include 'includes/footer.php'; ?>
