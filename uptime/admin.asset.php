<?php

security_check();
admin_check();

if(!isset($_GET['key']) || !is_numeric($_GET['key']))
{
    message_set('Asset Error', 'There was an error with the provided asset.');
    header_redirect('/admin/dashboard');
}

$asset_id = $_GET['key'];

$query = 'SELECT * FROM assets WHERE id = '.$asset_id.' LIMIT 1';
$result = mysqli_query($connect, $query);

if(!mysqli_num_rows($result))
{
    message_set('Asset Error', 'There was an error with the provided asset.');
    header_redirect('/admin/dashboard');
}

$asset = mysqli_fetch_assoc($result);

// Get uptime statistics for 24 hours
$stats_query = 'SELECT 
    COUNT(*) as total_checks,
    SUM(CASE WHEN up = 1 THEN 1 ELSE 0 END) as up_checks,
    AVG(response_time) as avg_response_time,
    MAX(response_time) as max_response_time,
    MIN(response_time) as min_response_time
FROM checks 
WHERE asset_id = '.$asset_id.' 
AND checked_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)';
$stats_result = mysqli_query($connect, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

$uptime_percentage = $stats['total_checks'] > 0 ? ($stats['up_checks'] / $stats['total_checks']) * 100 : 0;

// Get latest check
$latest_query = 'SELECT * FROM checks 
WHERE asset_id = '.$asset_id.' 
ORDER BY checked_at DESC 
LIMIT 1';
$latest_result = mysqli_query($connect, $latest_query);
$latest = mysqli_fetch_assoc($latest_result);

// Get all checks from last 24 hours for table
$checks_query = 'SELECT * FROM checks 
WHERE asset_id = '.$asset_id.' 
AND checked_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY checked_at DESC';
$checks_result = mysqli_query($connect, $checks_query);

// Get checks for charts (last 48 checks in chronological order)
$chart_query = 'SELECT * FROM checks 
WHERE asset_id = '.$asset_id.' 
ORDER BY checked_at DESC
LIMIT 48';
$chart_result = mysqli_query($connect, $chart_query);

// Store results in array and reverse to get chronological order
$chart_data = [];
while($chart_check = mysqli_fetch_assoc($chart_result)) {
    $chart_data[] = $chart_check;
}
$chart_data = array_reverse($chart_data);

$chart_labels = [];
$chart_response_times = [];
$chart_status = [];

foreach($chart_data as $chart_check) {
    $chart_labels[] = date('M j H:i', strtotime($chart_check['checked_at']));
    $chart_response_times[] = round($chart_check['response_time'], 2);
    $chart_status[] = $chart_check['up'] == 1 ? 100 : 0;
}

define('APP_NAME', 'Uptime');
define('PAGE_TITLE', 'Asset Details');
define('PAGE_SELECTED_SECTION', 'admin-dashboard');
define('PAGE_SELECTED_SUB_PAGE', '/admin/asset');

include('../templates/html_header.php');
include('../templates/nav_header.php');
include('../templates/nav_slideout.php');
include('../templates/nav_sidebar.php');
include('../templates/main_header.php');

include('../templates/message.php');

?>

<h1 class="w3-margin-top w3-margin-bottom">
    <img
        src="https://cdn.brickmmo.com/icons@1.0.0/uptime.png"
        height="50"
        style="vertical-align: top"
    />
    Uptime
</h1>
<p>
    <a href="<?=ENV_DOMAIN?>/admin/dashboard">Uptime</a> / 
    Asset Details
</p>

<hr>

<h2>Asset Details: <?=htmlspecialchars($asset['name'])?></h2>
<p class="w3-text-grey">
    <a href="<?=htmlspecialchars($asset['url'])?>" target="_blank">
        <i class="fas fa-external-link-alt fa-xs"></i>
        <?=htmlspecialchars($asset['url'])?>
    </a>
</p>

<?php if($asset['image']): ?>
    <img src="<?=$asset['image']?>" class="w3-image" style="max-width: 100%; border: 5px solid #848484; box-sizing: border-box;">
    <hr>
<?php endif; ?>

<!-- Current Status Summary -->
<?php if($latest): ?>
<div class="w3-row-padding w3-margin-bottom">
    <div class="w3-col l3 m6 s12">
        <div class="w3-card w3-white w3-padding w3-center">
            <i class="fas fa-<?=$latest['up'] == 1 ? 'check-circle' : 'times-circle'?> fa-3x w3-text-<?=$latest['up'] == 1 ? 'green' : 'red'?>"></i>
            <h3 class="w3-margin-top"><?=$latest['up'] == 1 ? 'UP' : 'DOWN'?></h3>
            Current Status
        </div>
    </div>
    
    <div class="w3-col l3 m6 s12">
        <div class="w3-card w3-white w3-padding w3-center">
            <i class="fas fa-clock fa-3x w3-text-orange"></i>
            <h3 class="w3-margin-top"><?=$latest['response_time'] ? round($latest['response_time'], 2) . 'ms' : 'N/A'?></h3>
            Response Time
        </div>
    </div>
    
    <div class="w3-col l3 m6 s12">
        <div class="w3-card w3-white w3-padding w3-center">
            <i class="fas fa-percentage fa-3x w3-text-blue"></i>
            <h3 class="w3-margin-top"><?=round($uptime_percentage, 1)?>%</h3>
            Uptime (24h)
        </div>
    </div>
    
    <div class="w3-col l3 m6 s12">
        <div class="w3-card w3-white w3-padding w3-center">
            <i class="fas fa-sync fa-3x w3-text-grey"></i>
            <h3 class="w3-margin-top"><?=time_elapsed_string($latest['checked_at'])?></h3>
            Last Check
        </div>
    </div>
</div>
<?php else: ?>
<div class="w3-panel w3-yellow w3-round w3-margin-bottom">
    <p><i class="fas fa-exclamation-triangle"></i> No monitoring data available yet.</p>
</div>
<?php endif; ?>

<hr>

<h2>24 Hour Performance</h2>

<div class="w3-card w3-white w3-padding w3-margin-bottom">
    <canvas id="performanceChart" style="max-height: 400px;"></canvas>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartLabels = <?=json_encode($chart_labels)?>;
    const responseTimesData = <?=json_encode($chart_response_times)?>;
    const statusData = <?=json_encode($chart_status)?>;

    if (typeof Chart === 'undefined') {
        console.error('Chart.js not loaded!');
        return;
    }

    const ctx = document.getElementById('performanceChart');
    if (ctx) {
        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [
                    {
                        label: 'Response Time (ms)',
                        data: responseTimesData,
                        borderColor: '#f06d21',
                        backgroundColor: 'rgba(240, 109, 33, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: '#f06d21',
                        yAxisID: 'y'
                    },
                    {
                        label: 'Status (UP/DOWN)',
                        data: statusData,
                        borderColor: '#4CAF50',
                        backgroundColor: 'rgba(76, 175, 80, 0.1)',
                        borderWidth: 2,
                        stepped: true,
                        fill: true,
                        pointRadius: 0,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2.5,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.dataset.label === 'Status (UP/DOWN)') {
                                    return 'Status: ' + (context.parsed.y === 100 ? 'UP' : 'DOWN');
                                }
                                return context.dataset.label + ': ' + context.parsed.y + 'ms';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Response Time (ms)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            callback: function(value) {
                                return value === 100 ? 'UP' : value === 0 ? 'DOWN' : '';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Status'
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
    }
});
</script>

<hr>

<h2>Recent Checks (24h)</h2>

<table class="w3-table w3-bordered w3-striped w3-margin-bottom">
    <tr>
        <th class="bm-table-icon">Status</th>
        <th>Time</th>
        <th>Response</th>
    </tr>

    <?php while($check = mysqli_fetch_assoc($checks_result)): ?>
    <tr>
        <td class="bm-table-icon">
            <span class="w3-tag <?=$check['up'] == 1 ? 'w3-green' : 'w3-red'?>">
                <?=$check['up'] == 1 ? 'Up' : 'Down'?>
            </span>
        </td>
        <td><?=time_elapsed_string($check['checked_at'])?></td>
        <td>
            <?=$check['response_time'] ? round($check['response_time'], 2) . 'ms' : 'N/A'?>
            <br>
            <small>
                HTTP: <?=htmlspecialchars(($check['response_code'] ?? $check['status_code'] ?? 'N/A'))?>
                <?php if($check['error_message']): ?>
                    <br>
                    <span class="w3-text-red"><?=htmlspecialchars($check['error_message'])?></span>
                <?php endif; ?>
            </small>
        </td>
    </tr>
    <?php endwhile; ?>

</table>

<?php

include('../templates/main_footer.php');
include('../templates/debug.php');
include('../templates/html_footer.php');
