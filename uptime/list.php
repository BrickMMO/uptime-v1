<?php

define('APP_NAME', 'QR Codes');
define('PAGE_TITLE', 'Dashboard');
define('PAGE_SELECTED_SECTION', '');
define('PAGE_SELECTED_SUB_PAGE', '');

include('../templates/html_header.php');
include('../templates/nav_header.php');
include('../templates/nav_slideout.php');
include('../templates/nav_sidebar.php');
include('../templates/main_header.php');

include('../templates/message.php');

$query = 'SELECT *
    FROM events
    WHERE ends_at > NOW()
    ORDER BY starts_at ASC
    LIMIT 5';
$result = mysqli_query($connect, $query);

?>

<main>
    
    <div class="w3-center">
        <h1>Upcoming Events</h1>
    </div>

    <hr>

    <div>

        <?php while ($record = mysqli_fetch_assoc($result)): ?>

            <div class="w3-card-4 w3-margin-top" style="max-width:100%; height: 100%;">
                <header class="w3-container w3-purple">
                    <h4 style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?=$record['name']?></h4>
                </header>

                <div class="w3-flex w3-padding">
                    
                    <div style="width: 200px;">
                        <a href="/details/<?=$record['id']?>">
                            <?php if($record['thumbnail']): ?>
                                <img src="<?=$record['thumbnail']?>" class="w3-image" style="max-wdith: 100%;">
                            <?php else: ?>
                                <img src="https://cdn.brickmmo.com/images@1.0.0/no_calendar.png" class="w3-image" style="max-wdith: 100%;">
                            <?php endif; ?>
                        </a>
                    </div>
                    
                    <div class="w3-padding" style="flex: 1;">
                        Date: <span class="w3-bold"><?=date_to_format($record['starts_at'], 'FULL')?></span>
                        <br>
                        Location: <span class="w3-bold"><?=$record['location']?></span>
                        <hr>
                        <a href="/details/<?=$record['id']?>">Event Details</a>
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