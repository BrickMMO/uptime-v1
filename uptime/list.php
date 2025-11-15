<?php

define('APP_NAME', 'Uptime');
define('PAGE_TITLE', 'Dashboard');
define('PAGE_SELECTED_SECTION', '');
define('PAGE_SELECTED_SUB_PAGE', '');

include('../templates/html_header.php');
include('../templates/nav_header.php');
include('../templates/nav_slideout.php');
include('../templates/nav_sidebar.php');
include('../templates/main_header.php');

include('../templates/message.php');

$query = 'SELECT a.*, 
    (SELECT up FROM checks WHERE asset_id = a.id ORDER BY checked_at DESC LIMIT 1) as current_status
    FROM assets a
    WHERE a.deleted_at IS NULL
    ORDER BY a.name';
$result = mysqli_query($connect, $query);

?>

<main>
    
    <div class="w3-center">
        <h1>Uptime Monitor</h1>
    </div>

    <hr>

    <table class="w3-table w3-bordered w3-striped">
        <tr>
            <th class="bm-table-icon"></th>
            <th>Asset</th>
            <th class="bm-table-icon">Status</th>
        </tr>

        <?php while ($record = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td>
                    <?php if($record['image']): ?>
                        <img src="<?=$record['image']?>" width="70">
                    <?php endif; ?>
                </td>
                <td>
                    <strong><?=$record['name']?></strong>
                    <br>
                    <small>
                        <a href="<?=$record['url']?>" target="_blank"><?=$record['url']?></a>
                    </small>
                </td>
                <td>
                    <?php if($record['current_status'] == 1): ?>
                        <span class="w3-tag w3-green">Up</span>
                    <?php elseif($record['current_status'] === '0'): ?>
                        <span class="w3-tag w3-red">Down</span>
                    <?php else: ?>
                        <span class="w3-tag w3-grey">Unknown</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>

    </table>

</main>

<?php

include('../templates/main_footer.php');
include('../templates/debug.php');
include('../templates/html_footer.php');