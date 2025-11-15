<?php
require_once '../config.php';
require_once '../monitor.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

include 'includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
    .asset-row { cursor: pointer; transition: background-color 0.2s; }
    .asset-row:hover { background-color: #f0f8ff !important; }
    .chart-section { display: none; padding: 20px; background: #f9f9f9; border-top: 2px solid #f06d21; }
    .chart-section.active { display: block; }
    .chart-container-small { 
        background: white; 
        border: 1px solid #ddd; 
        border-radius: 8px; 
        padding: 15px; 
        margin: 10px 0;
        min-height: 250px;
    }
</style>

<?php
$days = isset($_GET['days']) ? (int)$_GET['days'] : 7;
$asset_id = isset($_GET['asset']) ? (int)$_GET['asset'] : 0;
$stmt = $pdo->prepare("SELECT id, name FROM assets WHERE status = 1 ORDER BY name");
$stmt->execute();
$all_assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="w3-container w3-margin-top">
    <h2><i class="fas fa-chart-line w3-text-blue"></i> Uptime Reports</h2>
    
    <div class="w3-card w3-white w3-padding w3-margin-top">
        <h3><i class="fas fa-filter w3-text-orange"></i> Filters</h3>
        <form method="GET" class="w3-row-padding">
            <div class="w3-col l4 m6 s12">
                <label><b>Time Period</b></label>
                <select class="w3-select w3-border w3-round" name="days">
                    <option value="1" <?= $days == 1 ? 'selected' : '' ?>>Last 24 hours</option>
                    <option value="7" <?= $days == 7 ? 'selected' : '' ?>>Last 7 days</option>
                    <option value="30" <?= $days == 30 ? 'selected' : '' ?>>Last 30 days</option>
                    <option value="90" <?= $days == 90 ? 'selected' : '' ?>>Last 90 days</option>
                </select>
            </div>
            
            <div class="w3-col l4 m6 s12">
                <label><b>Asset</b></label>
                <select class="w3-select w3-border w3-round" name="asset">
                    <option value="0">All Assets</option>
                    <?php foreach ($all_assets as $asset): ?>
                    <option value="<?= $asset['id'] ?>" <?= $asset_id == $asset['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($asset['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="w3-col l4 m12 s12">
                <label><b>&nbsp;</b></label>
                <button type="submit" class="w3-button w3-blue w3-block">
                    <i class="fas fa-search"></i> Update Report
                </button>
            </div>
        </form>
    </div>
    
    <?php
    $where_conditions = ["uc.checked_at >= DATE_SUB(NOW(), INTERVAL ? DAY)"];
    $params = [$days];
    
    if ($asset_id > 0) {
        $where_conditions[] = "a.id = ?";
        $params[] = $asset_id;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT a.id) as asset_count,
            COUNT(uc.id) as total_checks,
            SUM(CASE WHEN uc.up = 1 THEN 1 ELSE 0 END) as up_checks,
            AVG(uc.response_time) as avg_response_time,
            MIN(uc.response_time) as min_response_time,
            MAX(uc.response_time) as max_response_time
        FROM assets a 
        JOIN uptime_checks uc ON a.id = uc.asset_id 
        WHERE a.status = 'active' AND $where_clause
    ");
    $stmt->execute($params);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $overall_uptime = $summary['total_checks'] > 0 ? ($summary['up_checks'] / $summary['total_checks']) * 100 : 0;
    ?>
    
    <div class="w3-card w3-white w3-padding w3-margin-top">
        <h3><i class="fas fa-chart-bar w3-text-green"></i> Summary Statistics</h3>
        <div class="w3-row-padding">
            <div class="w3-col l3 m6 s12">
                <div class="w3-center w3-padding">
                    <i class="fas fa-percentage w3-text-<?= $overall_uptime > 95 ? 'green' : ($overall_uptime > 85 ? 'orange' : 'red') ?> w3-jumbo"></i>
                    <h3><?= round($overall_uptime, 2) ?>%</h3>
                    <p>Overall Uptime</p>
                </div>
            </div>
            
            <div class="w3-col l3 m6 s12">
                <div class="w3-center w3-padding">
                    <i class="fas fa-check-circle w3-text-green w3-jumbo"></i>
                    <h3><?= number_format($summary['total_checks']) ?></h3>
                    <p>Total Checks</p>
                </div>
            </div>
            
            <div class="w3-col l3 m6 s12">
                <div class="w3-center w3-padding">
                    <i class="fas fa-clock w3-text-orange w3-jumbo"></i>
                    <h3><?= round($summary['avg_response_time'], 0) ?>ms</h3>
                    <p>Avg Response Time</p>
                </div>
            </div>
            
            <div class="w3-col l3 m6 s12">
                <div class="w3-center w3-padding">
                    <i class="fas fa-globe w3-text-blue w3-jumbo"></i>
                    <h3><?= $summary['asset_count'] ?></h3>
                    <p>Assets Monitored</p>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    $stmt = $pdo->prepare("
        SELECT 
            a.id,
            a.name,
            a.url,
            COUNT(uc.id) as total_checks,
            SUM(CASE WHEN uc.up = 1 THEN 1 ELSE 0 END) as up_checks,
            AVG(uc.response_time) as avg_response_time,
            MIN(uc.response_time) as min_response_time,
            MAX(uc.response_time) as max_response_time,
            SUM(CASE WHEN uc.up = 0 THEN 1 ELSE 0 END) as down_checks,
            SUM(CASE WHEN uc.up IS NULL THEN 1 ELSE 0 END) as error_checks
        FROM assets a 
        JOIN uptime_checks uc ON a.id = uc.asset_id 
        WHERE a.status = 'active' AND $where_clause
        GROUP BY a.id, a.name, a.url
        ORDER BY a.name
    ");
    $stmt->execute($params);
    $asset_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    
    <div class="w3-card w3-white w3-padding w3-margin-top">
        <h3><i class="fas fa-list w3-text-blue"></i> Per-Asset Statistics</h3>
        <div class="w3-responsive">
            <table class="w3-table w3-striped w3-bordered">
                <thead>
                    <tr class="w3-light-grey">
                        <th>Asset</th>
                        <th>Uptime %</th>
                        <th>Total Checks</th>
                        <th>Up/Down/Error</th>
                        <th>Avg Response</th>
                        <th>Min/Max Response</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($asset_stats as $stat): 
                        $asset_uptime = $stat['total_checks'] > 0 ? ($stat['up_checks'] / $stat['total_checks']) * 100 : 0;
                    ?>
                    <tr class="asset-row" onclick="toggleChart(<?= $stat['id'] ?>)" title="Click to view charts">
                        <td>
                            <i class="fas fa-chart-line" style="color: #f06d21; margin-right: 8px;"></i>
                            <strong><?= htmlspecialchars($stat['name']) ?></strong><br>
                            <small class="w3-text-grey"><?= htmlspecialchars($stat['url']) ?></small>
                        </td>
                        <td>
                            <div class="w3-light-grey w3-round" style="height: 16px; width: 100px;">
                                <div class="w3-round w3-<?= $asset_uptime > 95 ? 'green' : ($asset_uptime > 85 ? 'orange' : 'red') ?>" 
                                     style="width:<?= $asset_uptime ?>%; height: 16px;"></div>
                            </div>
                            <strong><?= round($asset_uptime, 2) ?>%</strong>
                        </td>
                        <td><?= number_format($stat['total_checks']) ?></td>
                        <td>
                            <span class="w3-tag w3-green w3-small w3-round"><?= $stat['up_checks'] ?></span>
                            <span class="w3-tag w3-red w3-small w3-round"><?= $stat['down_checks'] ?></span>
                            <span class="w3-tag w3-orange w3-small w3-round"><?= $stat['error_checks'] ?></span>
                        </td>
                        <td><?= round($stat['avg_response_time'], 0) ?>ms</td>
                        <td><?= round($stat['min_response_time'], 0) ?>ms / <?= round($stat['max_response_time'], 0) ?>ms</td>
                    </tr>
                    <tr id="chart-row-<?= $stat['id'] ?>" style="display: none;">
                        <td colspan="6">
                            <div class="chart-section" id="chart-<?= $stat['id'] ?>">
                                <h4 style="color: #333; margin-top: 0;">
                                    <i class="fas fa-chart-area" style="color: #f06d21;"></i> 
                                    Detailed Charts for <?= htmlspecialchars($stat['name']) ?>
                                </h4>
                                <div class="w3-row-padding">
                                    <div class="w3-col l6 m12 s12">
                                        <div class="chart-container-small">
                                            <h5 style="margin-top: 0; color: #666;">Response Time Trend</h5>
                                            <div style="position: relative; height: 200px;">
                                                <canvas id="responseChart-<?= $stat['id'] ?>"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="w3-col l6 m12 s12">
                                        <div class="chart-container-small">
                                            <h5 style="margin-top: 0; color: #666;">Uptime Status</h5>
                                            <div style="position: relative; height: 200px;">
                                                <canvas id="statusChart-<?= $stat['id'] ?>"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (empty($asset_stats)): ?>
        <div class="w3-center w3-padding-64">
            <i class="fas fa-chart-line w3-jumbo w3-text-grey"></i>
            <h3 class="w3-text-grey">No Data Available</h3>
            <p>No monitoring data found for the selected criteria.</p>
        </div>
        <?php endif; ?>
    </div>
    
    <?php
    $stmt = $pdo->prepare("
        SELECT 
            a.name,
            uc.up as status,
            uc.error_message,
            uc.checked_at,
            uc.response_time
        FROM checks uc
        JOIN assets a ON uc.asset_id = a.id
        WHERE a.status = 1 AND uc.up != 1 AND $where_clause
        ORDER BY uc.checked_at DESC
        LIMIT 100
    ");
    $stmt->execute($params);
    $downtime_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    
    <?php if (!empty($downtime_events)): ?>
    <div class="w3-card w3-white w3-padding w3-margin-top">
        <h3><i class="fas fa-exclamation-triangle w3-text-red"></i> Recent Downtime Events</h3>
        <div class="w3-responsive">
            <table class="w3-table w3-striped w3-bordered">
                <thead>
                    <tr class="w3-light-grey">
                        <th>Time</th>
                        <th>Asset</th>
                        <th>Status</th>
                        <th>Error Message</th>
                        <th>Response Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($downtime_events as $event): ?>
                    <tr>
                        <td><?= date('M j, H:i:s', strtotime($event['checked_at'])) ?></td>
                        <td><?= htmlspecialchars($event['name']) ?></td>
                        <td>
                            <span class="w3-tag w3-round w3-<?= $event['status'] === 'down' ? 'red' : 'orange' ?>">
                                <i class="fas fa-<?= $event['status'] === 'down' ? 'times' : 'exclamation' ?>"></i>
                                <?= strtoupper($event['status']) ?>
                            </span>
                        </td>
                        <td class="w3-text-red"><?= $event['error_message'] ? htmlspecialchars($event['error_message']) : 'No specific error' ?></td>
                        <td><?= $event['response_time'] ? round($event['response_time'], 2) . 'ms' : 'N/A' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
const chartInstances = {};
const chartData = {};

function toggleChart(assetId) {
    const chartRow = document.getElementById('chart-row-' + assetId);
    const chartSection = document.getElementById('chart-' + assetId);
    
    if (chartRow.style.display === 'none') {
        chartRow.style.display = 'table-row';
        chartSection.classList.add('active');
        
        if (!chartInstances[assetId]) {
            loadChartData(assetId);
        }
    } else {
        chartRow.style.display = 'none';
        chartSection.classList.remove('active');
    }
}

function loadChartData(assetId) {
    const days = <?= $days ?>;
    
    fetch('../monitor.php?action=get_chart_data&asset_id=' + assetId + '&days=' + days)
        .then(response => response.json())
        .then(data => {
            createCharts(assetId, data);
        })
        .catch(error => {
            console.error('Error loading chart data:', error);
            fetch('get_chart_data.php?asset_id=' + assetId + '&days=' + days)
                .then(response => response.json())
                .then(data => {
                    createCharts(assetId, data);
                })
                .catch(err => {
                    console.error('Fallback also failed:', err);
                    createDummyCharts(assetId);
                });
        });
}

function createDummyCharts(assetId) {
    <?php
    require_once '../monitor.php';
    foreach ($asset_stats as $stat):
        $recent_checks = $monitor->getRecentChecks($stat['id'], 50);
        $labels = [];
        $response_times = [];
        $statuses = [];
        
        foreach (array_reverse($recent_checks) as $check) {
            $labels[] = date('H:i', strtotime($check['checked_at']));
            $response_times[] = round($check['response_time'], 2);
            $statuses[] = $check['status'] === 'up' ? 100 : 0;
        }
    ?>
    
    if (assetId === <?= $stat['id'] ?>) {
        const labels = <?= json_encode($labels) ?>;
        const responseTimes = <?= json_encode($response_times) ?>;
        const statuses = <?= json_encode($statuses) ?>;
        
        createCharts(assetId, {
            labels: labels,
            response_times: responseTimes,
            statuses: statuses
        });
    }
    <?php endforeach; ?>
}

function createCharts(assetId, data) {
    const ctx1 = document.getElementById('responseChart-' + assetId);
    const ctx2 = document.getElementById('statusChart-' + assetId);
    
    if (!ctx1 || !ctx2) {
        console.error('Canvas elements not found for asset', assetId);
        return;
    }
    
    if (chartInstances[assetId]) {
        chartInstances[assetId].response.destroy();
        chartInstances[assetId].status.destroy();
    }
    
    chartInstances[assetId] = {};
    
    chartInstances[assetId].response = new Chart(ctx1.getContext('2d'), {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Response Time (ms)',
                data: data.response_times,
                borderColor: '#f06d21',
                backgroundColor: 'rgba(240, 109, 33, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointRadius: 2,
                pointBackgroundColor: '#f06d21'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'top' },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Response Time (ms)' }
                },
                x: {
                    title: { display: true, text: 'Time' },
                    ticks: { maxRotation: 45, minRotation: 45 }
                }
            }
        }
    });
    
    chartInstances[assetId].status = new Chart(ctx2.getContext('2d'), {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Status',
                data: data.statuses,
                borderColor: '#4CAF50',
                backgroundColor: 'rgba(76, 175, 80, 0.1)',
                borderWidth: 2,
                stepped: true,
                fill: true,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y === 100 ? 'UP' : 'DOWN';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value === 100 ? 'UP' : value === 0 ? 'DOWN' : '';
                        }
                    }
                },
                x: {
                    title: { display: true, text: 'Time' },
                    ticks: { maxRotation: 45, minRotation: 45 }
                }
            }
        }
    });
    
    console.log('Charts created for asset', assetId);
}
</script>

<?php include 'includes/footer.php'; ?>
