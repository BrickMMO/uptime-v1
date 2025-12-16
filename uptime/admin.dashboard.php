<?php

security_check();
admin_check();

if (isset($_GET['delete'])) 
{

    $query = 'DELETE FROM assets 
        WHERE id = '.$_GET['delete'].'
        LIMIT 1';
    mysqli_query($connect, $query);

    $query = 'DELETE FROM checks
        WHERE asset_id = '.$_GET['delete'];
    mysqli_query($connect, $query);

    message_set('Delete Success', 'Asset has been deleted.');
    header_redirect('/admin/dashboard');
    
}

define('APP_NAME', 'Uptime');
define('PAGE_TITLE', 'Dashboard');
define('PAGE_SELECTED_SECTION', 'admin-dashboard');
define('PAGE_SELECTED_SUB_PAGE', '/admin/dashboard');

include('../templates/html_header.php');
include('../templates/nav_header.php');
include('../templates/nav_slideout.php');
include('../templates/nav_sidebar.php');
include('../templates/main_header.php');

include('../templates/message.php');    

$query = 'SELECT assets.*, 
        checks.checked_at AS last_check,
        checks.up AS check_status,
        checks.response_time
    FROM assets
    LEFT JOIN checks ON assets.id = checks.asset_id 
        AND checks.checked_at = (
            SELECT MAX(checked_at) 
            FROM checks 
            WHERE asset_id = assets.id
        )
    ORDER BY assets.name ASC';    
$result = mysqli_query($connect, $query);

$assets_count = mysqli_num_rows($result);

// Get statistics for cards
$stats_query = 'SELECT 
    COUNT(DISTINCT a.id) as total_assets, 
    COUNT(c.id) as total_checks_24h, 
    SUM(CASE WHEN c.up = 1 THEN 1 ELSE 0 END) as up_checks_24h, 
    AVG(c.response_time) as avg_response_time_24h 
FROM assets a 
LEFT JOIN checks c ON a.id = c.asset_id 
WHERE a.status = 1 AND c.checked_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)';
$stats_result = mysqli_query($connect, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

if($stats['avg_response_time_24h'] === null) {
    $stats['avg_response_time_24h'] = 0;
}
if($stats['total_checks_24h'] === null) {
    $stats['total_checks_24h'] = 0;
}
if($stats['up_checks_24h'] === null) {
    $stats['up_checks_24h'] = 0;
}

$issues_query = 'SELECT COUNT(*) as issue_count 
    FROM checks c 
    JOIN assets a ON c.asset_id = a.id 
    WHERE c.up != 1 AND c.checked_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)';
$issues_result = mysqli_query($connect, $issues_query);
$issues = mysqli_fetch_assoc($issues_result);

$overall_uptime = $stats['total_checks_24h'] > 0 ? ($stats['up_checks_24h'] / $stats['total_checks_24h']) * 100 : 0;

?>

<!-- CONTENT -->

<h1 class="w3-margin-top w3-margin-bottom">
    <img
        src="https://cdn.brickmmo.com/icons@1.0.0/uptime.png"
        height="50"
        style="vertical-align: top"
    />
    Uptime
</h1>

<div class="w3-row-padding w3-margin-bottom">
    <div class="w3-col l3 m6 s12">
        <div class="w3-card w3-white w3-padding w3-center">
            <i class="fas fa-globe fa-3x w3-text-orange"></i>
            <h3 class="w3-margin-top"><?=$stats['total_assets']?></h3>
            Monitored Assets
        </div>
    </div>
    
    <div class="w3-col l3 m6 s12">
        <div class="w3-card w3-white w3-padding w3-center">
            <i class="fas fa-heartbeat fa-3x w3-text-green"></i>
            <h3 class="w3-margin-top"><?=round($overall_uptime, 1)?>%</h3>
            Overall Uptime (24h)
        </div>
    </div>
    
    <div class="w3-col l3 m6 s12">
        <div class="w3-card w3-white w3-padding w3-center">
            <i class="fas fa-clock fa-3x w3-text-blue"></i>
            <h3 class="w3-margin-top"><?=round($stats['avg_response_time_24h'], 0)?>ms</h3>
            Avg Response Time
        </div>
    </div>
    
    <div class="w3-col l3 m6 s12">
        <div class="w3-card w3-white w3-padding w3-center">
            <i class="fas fa-exclamation-triangle fa-3x w3-text-red"></i>
            <h3 class="w3-margin-top"><?=$issues['issue_count']?></h3>
            Issues (24h)
        </div>
    </div>
</div>

<hr />

<h2>Asset List</h2>

<?php if (mysqli_num_rows($result)): ?>

    <table class="w3-table w3-bordered w3-striped w3-margin-bottom">
        <tr>
            <th class="bm-table-icon"></th>
            <th>Name</th>
            <th>Last Check</th>
            <th class="bm-table-icon"></th>
            <th class="bm-table-icon"></th>
            <th class="bm-table-icon"></th>
        </tr>

        <?php while ($record = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td>
                    <?php if($record['image']): ?>
                        <img src="<?=$record['image']?>" width="70" style="border: 1px solid #848484; box-sizing: border-box;">
                    <?php endif; ?>
                </td>
                <td>
                    <?=$record['name'] ?>
                    <br>
                    <small>
                        URL: <a href="<?=$record['url']?>"><?=$record['url']?></a>
                    </small>
                </td>
                <td>
                    <?php if($record['last_check']): ?>
                        <span class="w3-tag <?=$record['check_status'] == 1 ? 'w3-green' : 'w3-red'?>">
                            <?=$record['check_status'] == 1 ? 'Up' : 'Down'?>
                        </span>
                        <br>
                        <small>
                            <?=time_elapsed_string($record['last_check'])?>
                            <?php if($record['response_time']): ?>
                                <br>
                                Response: <?=round($record['response_time'], 2)?>ms
                            <?php endif; ?>
                        </small>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="<?=ENV_DOMAIN?>/details/<?=$record['id'] ?>">
                        <i class="fa-solid fa-chart-line"></i>
                    </a>
                </td>
                <td>
                    <a href="<?=ENV_DOMAIN?>/admin/edit/<?=$record['id'] ?>">
                        <i class="fa-solid fa-pencil"></i>
                    </a>
                </td>
                <td>
                    <a href="#" onclick="return confirmModal('Are you sure you want to delete the asset <?=$record['name'] ?>?', '/admin/dashboard/delete/<?=$record['id'] ?>');">
                        <i class="fa-solid fa-trash-can"></i>
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>

    </table>

<?php else: ?>

    <div class="w3-panel w3-light-grey w3-border-green">
        <p>
            <i class="fas fa-check-circle"></i>
            No issues assets being monitored!
        </p>
    </div>

<?php endif; ?>

<a
    href="<?=ENV_DOMAIN?>/admin/add"
    class="w3-button w3-white w3-border"
>
    <i class="fa-solid fa-pen-to-square fa-padding-right"></i> Add Asset
</a>


<!--
<a
    href="<?=ENV_DOMAIN?>/admin/import"
    class="w3-button w3-white w3-border"
>
    <i class="fa-solid fa-download"></i> Import Colours
</a>

<hr />

<div
    class="w3-row-padding"
    style="margin-left: -16px; margin-right: -16px"
>
    <div class="w3-half">
        <div class="w3-card">
            <header class="w3-container w3-grey w3-padding w3-text-white">
                <i class="bm-colours"></i> Uptime Status
            </header>
            <div class="w3-container w3-padding">Uptime Status Summary</div>
            <footer class="w3-container w3-border-top w3-padding">
                <a
                    href="<?=ENV_DOMAIN?>/admin/uptime/colours"
                    class="w3-button w3-border w3-white"
                >
                    <i class="fa-regular fa-file-lines fa-padding-right"></i>
                    Full Report
                </a>
            </footer>
        </div>
    </div>
    <div class="w3-half">
        <div class="w3-card">
            <header class="w3-container w3-grey w3-padding w3-text-white">
                <i class="bm-colours"></i> Stat Summary
            </header>
            <div class="w3-container w3-padding">App Statistics Summary</div>
            <footer class="w3-container w3-border-top w3-padding">
                <a
                    href="<?=ENV_DOMAIN?>/stats/colours"
                    class="w3-button w3-border w3-white"
                >
                    <i class="fa-regular fa-chart-bar fa-padding-right"></i> Full Report
                </a>
            </footer>
        </div>
    </div>
</div>
-->

<?php

include('../templates/main_footer.php');
include('../templates/debug.php');
include('../templates/html_footer.php');
