<?php

use \WideImage\WideImage;

security_check();
admin_check();

if(
    isset($_GET['delete']) && 
    is_numeric($_GET['delete']))
{
    $query = 'UPDATE events SET
        thumbnail = NULL
        WHERE id = '.$_GET['delete'].'
        LIMIT 1';
    mysqli_query($connect, $query);
    
    message_set('Thumbnail Delete Success', 'Event thumbnail has been deleted.');
    header_redirect('/admin/thumbnail/delete/'.$_GET['delete']);
}

if(
    !isset($_GET['key']) || 
    !is_numeric($_GET['key']))
{
    message_set('Event Error', 'There was an error with the provided event.');
    header_redirect('/admin/dashboard');
}
elseif ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    
    // Basic serverside validation
    if (
        !validate_image($_FILES['thumbnail']))
    {
        message_set('Thumbnail Upload Error', 'There was an error with your uploaded image.', 'red');
        header_redirect('/admin/thumbnail?key='.$_GET['key']);
    }

    $image = Wideimage::load($_FILES['thumbnail']['tmp_name']);
    $image = $image->resize(400, 400, 'outside');
    $image = $image->crop('center', 'center', 400, 400);
    $image = 'data:image/jpeg;base64, '.base64_encode($image->asString('jpg'));

    $query = 'UPDATE events SET
        thumbnail = "'.addslashes($image).'"
        WHERE id = '.$_GET['key'].'
        LIMIT 1';
    mysqli_query($connect, $query);
    
    message_set('Thumbnail Upload Success', 'Event thumbnail has been updated.');
    header_redirect('/admin/dashboard');
    
}

define('APP_NAME', 'Events');
define('PAGE_TITLE', 'Event Thumbnail');
define('PAGE_SELECTED_SECTION', 'events');
define('PAGE_SELECTED_SUB_PAGE', '/admin/dashboard');

include('../templates/html_header.php');
include('../templates/nav_header.php');
include('../templates/nav_slideout.php');
include('../templates/nav_sidebar.php');
include('../templates/main_header.php');

include('../templates/message.php');

$query = 'SELECT *
    FROM events
    WHERE id = "'.$_GET['key'].'"
    LIMIT 1';
$result = mysqli_query($connect, $query);
$record = mysqli_fetch_assoc($result);

?>

<!-- CONTENT -->

<h1 class="w3-margin-top w3-margin-bottom">
    <img
        src="https://cdn.brickmmo.com/icons@1.0.0/events.png"
        height="50"
        style="vertical-align: top"
    />
    Events
</h1>

<p>
    <a href="/admin/dashboard">Events</a> / 
    Event Thumbnail
</p>
<hr />

<h2>Event Thumbnail: <?=$record['name']?></h2>

<?php if($record['thumbnail']): ?>
    
    <div class="w3-margin-bottom">
        <img src="<?=$record['thumbnail']?>" alt="Current Thumbnail" style="max-width: 100%; height: auto;" />
    </div>
            
    <a href="#" class="w3-button w3-white w3-border" onclick="return confirmModal('Are you sure you want to delete the thumbnail for <?=$record['name']?>?', '/admin/thumbnail/delete/<?=$_GET['key']?>');">
        <i class="fa-solid fa-trash fa-padding-right"></i>
        Delete Thumbnail
    </a>
            
        
<?php endif; ?>

<p>The event thumbnail must be a jpg, png, or gif. Images will be resized and cropped to 400 x 400.</p>

<form
    enctype="multipart/form-data"
    method="post"
    novalidate
    id="main-form"
>

    <input  
        name="thumbnail" 
        class="w3-input w3-border" 
        type="file" 
        id="thumbnail" 
        autocomplete="off"
    />

    <button class="w3-block w3-btn w3-orange w3-text-white w3-margin-top">
        <i class="fa-solid fa-image fa-padding-right"></i>
        Update Thumbnail
    </button>
</form>
    
<?php

include('../templates/main_footer.php');
include('../templates/html_footer.php');
