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

$status = $check['status'] ?? 'unknown';
$response_time = isset($check['response_time']) ? htmlspecialchars(round($check['response_time']) . ' ms') : 'N/A';
$http_status = htmlspecialchars($check['http_code'] ?? ($check['status_code'] ?? 'N/A'));
$uptime_pct = htmlspecialchars($uptime_24h['uptime_percentage'] ?? 0);

$last_downtime = 'N/A';
try {
    $recent = $monitor->getRecentChecks($asset_id, 500);
    foreach ($recent as $rc) {
        if (isset($rc['status']) && $rc['status'] === 'down') {
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

$recent_checks = $monitor->getRecentChecks($asset_id, 50);
$chart_labels = [];
$chart_response_times = [];
$chart_status = [];

foreach (array_reverse($recent_checks) as $check) {
    $chart_labels[] = date('H:i', strtotime($check['checked_at']));
    $chart_response_times[] = round($check['response_time'], 2);
    $chart_status[] = $check['status'] === 'up' ? 100 : 0;
}

include 'includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
    .report-hero { background: #f5f7f8; padding: 18px; border-radius: 8px; }
    .status-badge { display:flex; align-items:center; gap:12px; }
    .status-badge .up { color: #4CAF50; font-weight:700; font-size:30px; }
    .report-title { font-size:48px; font-weight:700; color:#666; }
    .metric-card { border:2px solid #ff6b4a; border-radius:12px; padding:18px; text-align:center; background:white; }
    .metric-card .label { color:#666; }
    .metric-card .value { color:#ff6b4a; font-size:32px; font-weight:800; }
    .chart-container { 
        background: white; 
        border: 2px solid #ff6b4a; 
        border-radius: 12px; 
        padding: 20px; 
        margin-top: 20px;
        min-height: 300px;
    }
</style>

<div class="w3-container w3-margin-top">
    <div class="w3-row report-hero">
        <div class="w3-col s2 status-badge">
            <?php if ($status === 'up'): ?>
                <div class="up"><i class="fas fa-check-double"></i> UP</div>
            <?php else: ?>
                <div class="up" style="color:#f44336;"><i class="fas fa-times-circle"></i> DOWN</div>
            <?php endif; ?>
        </div>
        <div class="w3-col s8">
            <div class="report-title"><?= htmlspecialchars($asset['name'] ?: 'Display Asset') ?></div>
            <div class="w3-text-orange">Last checked: <?= $last_checked ?></div>
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

<div class="w3-container w3-margin-top">
    <div class="w3-row-padding">
        <div class="w3-col l6 m12 s12">
            <div class="chart-container">
                <h3 style="color: #333; margin-top: 0;"><i class="fas fa-chart-line" style="color: #f06d21;"></i> Response Time History</h3>
                <div style="position: relative; height: 250px;">
                    <canvas id="responseTimeChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="w3-col l6 m12 s12">
            <div class="chart-container">
                <h3 style="color: #333; margin-top: 0;"><i class="fas fa-heartbeat" style="color: #f06d21;"></i> Uptime Status</h3>
                <div style="position: relative; height: 250px;">
                    <canvas id="uptimeChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Chart.js loaded:', typeof Chart !== 'undefined');
    
    const chartLabels = <?= json_encode($chart_labels) ?>;
    const responseTimesData = <?= json_encode($chart_response_times) ?>;
    const statusData = <?= json_encode($chart_status) ?>;
    
    console.log('Chart data:', {
        labels: chartLabels.length,
        responseTimes: responseTimesData.length,
        status: statusData.length
    });

    if (typeof Chart === 'undefined') {
        console.error('Chart.js not loaded!');
        return;
    }

    const ctx1 = document.getElementById('responseTimeChart');
    if (!ctx1) {
        console.error('Canvas element responseTimeChart not found!');
        return;
    }
    
    new Chart(ctx1.getContext('2d'), {
    type: 'line',
    data: {
        labels: chartLabels,
        datasets: [{
            label: 'Response Time (ms)',
            data: responseTimesData,
            borderColor: '#f06d21',
            backgroundColor: 'rgba(240, 109, 33, 0.1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true,
            pointRadius: 3,
            pointBackgroundColor: '#f06d21'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                mode: 'index',
                intersect: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Response Time (ms)'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Time'
                },
                ticks: {
                    maxRotation: 45,
                    minRotation: 45
                }
            }
        }
    }
});

const ctx2 = document.getElementById('uptimeChart');
if (!ctx2) {
    console.error('Canvas element uptimeChart not found!');
    return;
}

new Chart(ctx2.getContext('2d'), {
    type: 'line',
    data: {
        labels: chartLabels,
        datasets: [{
            label: 'Status',
            data: statusData,
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
            legend: {
                display: true,
                position: 'top'
            },
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
                title: {
                    display: true,
                    text: 'Time'
                },
                ticks: {
                    maxRotation: 45,
                    minRotation: 45
                }
            }
        }
    }
});

console.log('Charts initialized successfully');
});
</script>

<?php include 'includes/footer.php'; ?>
