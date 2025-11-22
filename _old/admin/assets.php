<?php
require_once '../config.php';
require_once '../monitor.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$action = $_GET['action'] ?? 'list';
$asset_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if ($name && $url) {
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $stmt = $pdo->prepare("INSERT INTO assets (name, url, description) VALUES (?, ?, ?)");
                if ($stmt->execute([$name, $url, $description])) {
                    $message = 'Asset added successfully!';
                    $action = 'list';
                } else {
                    $error = 'Error adding asset.';
                }
            } else {
                $error = 'Please enter a valid URL.';
            }
        } else {
            $error = 'Please fill in all required fields.';
        }
    } elseif ($action === 'edit' && $asset_id) {
        $name = trim($_POST['name'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'active';
        
        if ($name && $url) {
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                try {
                    $stmt = $pdo->prepare("UPDATE assets SET name = ?, url = ?, description = ?, status = ? WHERE id = ?");
                    $success = $stmt->execute([$name, $url, $description, $status, $asset_id]);
                } catch (PDOException $e) {
                    $stmt = $pdo->prepare("UPDATE assets SET name = ?, url = ?, description = ?, status = ? WHERE asset_id = ?");
                    $success = $stmt->execute([$name, $url, $description, $status, $asset_id]);
                }
                
                if ($success) {
                    $message = 'Asset updated successfully!';
                    $action = 'list';
                } else {
                    $error = 'Error updating asset.';
                }
            } else {
                $error = 'Please enter a valid URL.';
            }
        } else {
            $error = 'Please fill in all required fields.';
        }
    }
}

if ($action === 'delete' && $asset_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM assets WHERE id = ?");
        $success = $stmt->execute([$asset_id]);
    } catch (PDOException $e) {
        $stmt = $pdo->prepare("DELETE FROM assets WHERE asset_id = ?");
        $success = $stmt->execute([$asset_id]);
    }
    
    if ($success) {
        $message = 'Asset deleted successfully!';
    } else {
        $error = 'Error deleting asset.';
    }
    $action = 'list';
}

$asset_data = null;
if ($action === 'edit' && $asset_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM assets WHERE id = ? LIMIT 1");
        $stmt->execute([$asset_id]);
        $asset_data = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM assets WHERE asset_id = ? LIMIT 1");
            $stmt->execute([$asset_id]);
            $asset_data = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e2) {
            $asset_data = null;
        }
    }
    
    if (!$asset_data) {
        $error = 'Asset not found.';
        $action = 'list';
    }
}

include 'includes/header.php';
?>

<div class="w3-container w3-margin-top">
    <div class="w3-row">
        <div class="w3-col s10">
            <h2>
                <i class="fas fa-globe w3-text-blue"></i> 
                <?php if ($action === 'add'): ?>
                    Add New Asset
                <?php elseif ($action === 'edit'): ?>
                    Edit Asset
                <?php else: ?>
                    Manage Assets
                <?php endif; ?>
            </h2>
        </div>
        <div class="w3-col s2 w3-right-align">
            <?php if ($action === 'list'): ?>
                <a href="assets.php?action=add" class="w3-button w3-green w3-round">
                    <i class="fas fa-plus"></i> Add Asset
                </a>
            <?php else: ?>
                <a href="assets.php" class="w3-button w3-blue w3-round">
                    <i class="fas fa-list"></i> Back to List
                </a>
            <?php endif; ?>
        </div>
    </div>
    
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
    
    <?php if ($action === 'add' || $action === 'edit'): ?>
    <div class="w3-card w3-white w3-margin-top w3-padding">
        <form method="POST">
            <div class="w3-row-padding">
                <div class="w3-col l6 m12 s12">
                    <label class="w3-text-grey"><b>Asset Name *</b></label>
                    <input class="w3-input w3-border w3-round w3-margin-bottom" 
                           type="text" name="name" 
                           value="<?= $asset_data ? htmlspecialchars($asset_data['name']) : '' ?>" 
                           required>
                    
                    <label class="w3-text-grey"><b>URL *</b></label>
                    <input class="w3-input w3-border w3-round w3-margin-bottom" 
                           type="url" name="url" 
                           value="<?= $asset_data ? htmlspecialchars($asset_data['url']) : '' ?>" 
                           required>
                    
                    <?php if ($action === 'edit'): ?>
                    <label class="w3-text-grey"><b>Status</b></label>
                    <select class="w3-select w3-border w3-round w3-margin-bottom" name="status">
                        <option value="active" <?= $asset_data && $asset_data['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $asset_data && $asset_data['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                    <?php endif; ?>
                </div>
                
                <div class="w3-col l6 m12 s12">
                    <label class="w3-text-grey"><b>Description</b></label>
                    <textarea class="w3-input w3-border w3-round w3-margin-bottom" 
                              name="description" rows="4"><?= $asset_data ? htmlspecialchars($asset_data['description']) : '' ?></textarea>
                </div>
            </div>
            
            <div class="w3-margin-top">
                <button type="submit" class="w3-button w3-blue w3-round">
                    <i class="fas fa-save"></i> <?= $action === 'add' ? 'Add Asset' : 'Update Asset' ?>
                </button>
                <a href="assets.php" class="w3-button w3-light-grey w3-round">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
    
    <?php else: ?>
    <div class="w3-card w3-white w3-margin-top w3-padding">
        <?php
        try {
            $stmt = $pdo->prepare("
                SELECT a.*, 
                       uc.up as latest_status,
                       uc.response_time as latest_response_time,
                       uc.checked_at as last_check,
                       COUNT(uc24.id) as total_checks_24h,
                       SUM(CASE WHEN uc24.up = 1 THEN 1 ELSE 0 END) as up_checks_24h
                FROM assets a
                LEFT JOIN checks uc ON a.id = uc.asset_id
                LEFT JOIN (
                    SELECT asset_id, MAX(checked_at) as max_check
                    FROM checks
                    GROUP BY asset_id
                ) latest ON a.id = latest.asset_id AND uc.checked_at = latest.max_check
                LEFT JOIN checks uc24 ON a.id = uc24.asset_id AND uc24.checked_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY a.id
                ORDER BY a.name
            ");
            $stmt->execute();
            $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM assets ORDER BY name");
                $stmt->execute();
                $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e2) {
                $assets = [];
                $error = 'Unable to load assets: ' . $e2->getMessage();
            }
        }

        foreach ($assets as &$a) {
            if (empty($a['id']) && isset($a['asset_id'])) {
                $a['id'] = $a['asset_id'];
            }
            if (!isset($a['latest_status'])) $a['latest_status'] = null;
            if (!isset($a['latest_response_time'])) $a['latest_response_time'] = null;
            if (!isset($a['last_check'])) $a['last_check'] = null;
            if (!isset($a['checks_24h'])) $a['checks_24h'] = 0;
            if (!isset($a['up_checks_24h'])) $a['up_checks_24h'] = 0;
        }
        unset($a);
        ?>
        
        <div class="w3-responsive">
            <table class="w3-table w3-striped w3-bordered">
                <thead>
                    <tr class="w3-light-grey">
                        <th>Asset</th>
                        <th>Status</th>
                        <th>24h Uptime</th>
                        <th>Response Time</th>
                        <th>Last Check</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assets as $asset): 
                        $uptime_24h = $asset['checks_24h'] > 0 ? ($asset['up_checks_24h'] / $asset['checks_24h']) * 100 : 0;
                    ?>
                    <tr>
                        <td>
                            <div class="w3-row">
                                <div class="w3-col s1">
                                    <i class="fas fa-circle w3-text-<?= $asset['status'] === 'active' ? 'green' : 'grey' ?>" 
                                       title="<?= ucfirst($asset['status']) ?>"></i>
                                </div>
                                <div class="w3-col s11">
                                    <strong><?= htmlspecialchars($asset['name']) ?></strong><br>
                                    <small class="w3-text-grey"><?= htmlspecialchars($asset['url']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if ($asset['latest_status']): ?>
                            <span class="w3-tag w3-round w3-<?= $asset['latest_status'] === 1 ? 'green' : ($asset['latest_status'] === 0 ? 'red' : 'orange') ?>">
                                <i class="fas fa-<?= $asset['latest_status'] === 1 ? 'check' : ($asset['latest_status'] === 0 ? 'times' : 'exclamation') ?>"></i>
                                <?= strtoupper($asset['latest_status']) ?>
                            </span>
                            <?php else: ?>
                            <span class="w3-tag w3-grey w3-round">NO DATA</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($asset['checks_24h'] > 0): ?>
                            <div class="w3-light-grey w3-round" style="height: 16px; width: 100px;">
                                <div class="w3-round w3-<?= $uptime_24h > 95 ? 'green' : ($uptime_24h > 85 ? 'orange' : 'red') ?>" 
                                     style="width:<?= $uptime_24h ?>%; height: 16px;"></div>
                            </div>
                            <small><?= round($uptime_24h, 1) ?>% (<?= $asset['up_checks_24h'] ?>/<?= $asset['checks_24h'] ?>)</small>
                            <?php else: ?>
                            <span class="w3-text-grey">No data</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $asset['latest_response_time'] ? round($asset['latest_response_time'], 2) . 'ms' : 'N/A' ?></td>
                        <td><?= $asset['last_check'] ? date('M j, H:i', strtotime($asset['last_check'])) : 'Never' ?></td>
                        <td>
                            <a href="../asset.php?id=<?= $asset['id'] ?>" class="w3-button w3-blue w3-small w3-round" title="View Details">
                                <i class="fas fa-chart-line"></i>
                            </a>
                            <a href="assets.php?action=edit&id=<?= $asset['id'] ?>" class="w3-button w3-orange w3-small w3-round" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="assets.php?action=delete&id=<?= $asset['id'] ?>" 
                               class="w3-button w3-red w3-small w3-round" 
                               title="Delete"
                               onclick="return confirm('Are you sure you want to delete this asset? This will also delete all monitoring data.')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (empty($assets)): ?>
        <div class="w3-center w3-padding-64">
            <i class="fas fa-globe w3-jumbo w3-text-grey"></i>
            <h3 class="w3-text-grey">No Assets Found</h3>
            <p>Start by adding your first asset to monitor.</p>
            <a href="assets.php?action=add" class="w3-button w3-blue w3-large w3-round">
                <i class="fas fa-plus"></i> Add Your First Asset
            </a>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
