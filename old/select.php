<?php
require_once 'config.php';
require_once 'monitor.php';

$stmt = $pdo->prepare("SELECT * FROM assets WHERE status = 1 ORDER BY name");
$stmt->execute();
$assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<style>
    body {
        background-color: #f5f5f5;
        overflow: hidden;
    }
    .select-page-container {
        position: fixed;
        top: 50px;
        left: 0;
        right: 0;
        bottom: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        overflow: hidden;
    }
    .select-content-wrapper {
        max-width: 700px;
        width: 100%;
        background: white;
        padding: 40px 50px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        position: relative;
        z-index: 10;
    }
    .select-content-wrapper h1 {
        font-size: 38px;
        font-weight: bold;
        margin-bottom: 8px;
        color: #333;
    }
    .select-content-wrapper h3 {
        font-size: 24px;
        margin-bottom: 15px;
    }
    .select-content-wrapper p {
        font-size: 13px;
        line-height: 1.5;
        margin-bottom: 30px;
        color: #999;
    }
    .lego-tower-left {
        position: fixed;
        left: 0;
        bottom: 60px;
        height: 550px;
        width: auto;
        z-index: 5;
    }
    .lego-worker-right {
        position: fixed;
        right: 0;
        bottom: 60px;
        height: 550px;
        width: auto;
        z-index: 5;
    }
    .form-section {
        background: #f9f9f9;
        padding: 25px;
        border-radius: 8px;
        margin-top: 15px;
    }
    .form-section label {
        display: block;
        margin-bottom: 10px;
        font-weight: 500;
        color: #333;
        font-size: 14px;
    }
    .form-section select {
        margin-bottom: 20px;
    }
    .check-button {
        background-color: #d32f2f !important;
        color: white !important;
        padding: 12px 45px !important;
        font-size: 18px !important;
        font-weight: bold !important;
        border: none !important;
    }
    @media (max-width: 1400px) {
        .lego-tower-left, .lego-worker-right {
            height: 500px;
        }
    }
    @media (max-width: 1200px) {
        .lego-tower-left, .lego-worker-right {
            height: 450px;
        }
    }
    @media (max-width: 992px) {
        .lego-tower-left, .lego-worker-right {
            height: 380px;
        }
    }
    @media (max-width: 768px) {
        .lego-tower-left, .lego-worker-right {
            display: none;
        }
        .select-content-wrapper {
            padding: 30px 20px;
        }
    }
</style>

<div class="select-page-container">
    <img src="assets/1.png" alt="Lego Tower" class="lego-tower-left">
    <img src="assets/2.png" alt="Lego Worker" class="lego-worker-right">
    
    <div class="select-content-wrapper">
        <h1 class="w3-center">MONITOR BRICKMMO ASSETS</h1>
        <h3 class="w3-center w3-text-orange">Monitor Your Assets</h3>
        <p class="w3-center">Select any BrickMMO asset to view its current status, uptime history, response time metrics, and detailed performance reports. Get instant insights into the health of your digital infrastructure.</p>

        <div class="form-section">
            <form action="reports.php" method="get">
                <label>Select Asset to Check</label>
                <select id="assetSelect" name="id" class="w3-select w3-border w3-round w3-large" required>
                    <option value="">-- Choose an asset --</option>
                    <?php foreach ($assets as $asset): 
                        $val = $asset['id'];
                        $name = htmlspecialchars($asset['name']);
                        $url = htmlspecialchars($asset['url']);
                    ?>
                    <option value="<?= $val ?>" data-name="<?= $name ?>" data-url="<?= $url ?>"><?= $name ?> - <?= $url ?></option>
                    <?php endforeach; ?>
                </select>

                <p class="w3-center">
                    <button type="submit" class="w3-button w3-round check-button">Check Status</button>
                </p>
            </form>
        </div>

        
        <script>
        (function(){
            const select = document.getElementById('assetSelect');
            if (!select) return;
        })();
        </script>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

