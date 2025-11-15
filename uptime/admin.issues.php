<?php

security_check();
admin_check();

define('APP_NAME', 'Uptime');
define('PAGE_TITLE', 'Recent Issues');
define('PAGE_SELECTED_SECTION', 'admin-dashboard');
define('PAGE_SELECTED_SUB_PAGE', '/admin/issues');

include('../templates/html_header.php');
include('../templates/nav_header.php');
include('../templates/nav_slideout.php');
include('../templates/nav_sidebar.php');
include('../templates/main_header.php');

include('../templates/message.php');

// Get recent issues from last 24 hours
$query = 'SELECT a.name, a.url, c.up, c.error_message, c.response_time, c.checked_at 
    FROM checks c 
    JOIN assets a ON c.asset_id = a.id 
    WHERE c.up != 1 
    AND c.checked_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) 
    ORDER BY c.checked_at DESC';
$result = mysqli_query($connect, $query);

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
    <a href="/admin/dashboard">Uptime</a> / 
    Recent Issues
</p>

<hr>

<h2>Recent Issues (24h)</h2>

<?php if(mysqli_num_rows($result)): ?>

<div class="w3-responsive">
    <table class="w3-table w3-striped w3-bordered">
        <thead>
            <tr class="w3-light-grey">
                <th>Time</th>
                <th>Asset</th>
                <th>URL</th>
                <th>Response Time</th>
                <th>Error Message</th>
            </tr>
        </thead>
        <tbody>
            <?php while($issue = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?=date('M j, H:i:s', strtotime($issue['checked_at']))?></td>
                <td><strong><?=htmlspecialchars($issue['name'])?></strong></td>
                <td>
                    <small class="w3-text-grey">
                        <?=htmlspecialchars($issue['url'])?>
                    </small>
                </td>
                <td>
                    <?=$issue['response_time'] ? round($issue['response_time'], 2) . 'ms' : 'N/A'?>
                </td>
                <td class="w3-text-red">
                    <?=$issue['error_message'] ? htmlspecialchars($issue['error_message']) : 'No specific error message'?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php else: ?>

<div class="w3-panel w3-pale-green w3-border w3-border-green">
    <p>
        <i class="fas fa-check-circle"></i>
        No issues detected in the last 24 hours!
    </p>
</div>

<?php endif; ?>

<?php

include('../templates/main_footer.php');
include('../templates/debug.php');
include('../templates/html_footer.php');
