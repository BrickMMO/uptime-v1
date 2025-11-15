<?php

define('APP_NAME', 'Uptime');
define('PAGE_TITLE', 'Monitored Assets');
define('PAGE_SELECTED_SECTION', '');
define('PAGE_SELECTED_SUB_PAGE', '');

include('../templates/html_header.php');
include('../templates/nav_header.php');
include('../templates/nav_slideout.php');
include('../templates/nav_sidebar.php');
include('../templates/main_header.php');

include('../templates/message.php');

$query = 'SELECT a.*, 
    (SELECT up FROM checks WHERE asset_id = a.id ORDER BY checked_at DESC LIMIT 1) as current_status,
    (SELECT response_time FROM checks WHERE asset_id = a.id ORDER BY checked_at DESC LIMIT 1) as response_time,
    (SELECT checked_at FROM checks WHERE asset_id = a.id ORDER BY checked_at DESC LIMIT 1) as last_check
    FROM assets a
    WHERE a.deleted_at IS NULL
    ORDER BY a.name';
$result = mysqli_query($connect, $query);

?>

<main>
    
    <div class="w3-center">
        <h1>Monitored Assets</h1>
    </div>

    <hr>

    <div>

        <?php while ($record = mysqli_fetch_assoc($result)): ?>

            <div class="w3-card-4 w3-margin-top" style="max-width:100%; height: 100%;">
                <header class="w3-container w3-blue">
                    <h4 style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?=$record['name']?></h4>
                </header>

                <div class="w3-flex w3-padding">
                    
                    <div style="width: 300px;">
                        <?php if($record['image']): ?>
                            <img src="<?=$record['image']?>" class="w3-image" style="max-width: 100%; border: 5px solid #848484; box-sizing: border-box;">
                        <?php else: ?>
                            <img src="https://cdn.brickmmo.com/images@1.0.0/no_screenshot.png" class="w3-image" style="max-width: 100%; border: 5px solid #848484; box-sizing: border-box;">
                        <?php endif; ?>
                    </div>
                    
                    <div class="w3-padding" style="flex: 1;">
                        Status: 
                        <?php if($record['current_status'] == 1): ?>
                            <span class="w3-tag w3-green">Up</span>
                        <?php elseif($record['current_status'] === '0'): ?>
                            <span class="w3-tag w3-red">Down</span>
                        <?php else: ?>
                            <span class="w3-tag w3-grey">Unknown</span>
                        <?php endif; ?>
                        <br>
                        URL: <span class="w3-bold"><a href="<?=$record['url']?>"><?=$record['url']?></a></span>
                        <?php if($record['response_time']): ?>
                            <br>
                            Response Time: <span class="w3-bold"><?=round($record['response_time'], 2)?>ms</span>
                        <?php endif; ?>
                        <?php if($record['last_check']): ?>
                            <br>
                            Last Check: <span class="w3-bold"><?=time_elapsed_string($record['last_check'])?></span>
                        <?php endif; ?>
                        <hr>
                        <a href="/details/<?=$record['id']?>">Asset Details</a>
                    </div>
                </div>
            </div>

        <?php endwhile; ?>

    </div>

</main>

<?php

include('../templates/main_footer.php');
include('../templates/debug.php');
include('../templates/html_footer.php');