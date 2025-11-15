<?php

security_check();
admin_check();

if(
    !isset($_GET['key']) || 
    !is_numeric($_GET['key']))
{
    message_set('Tag Error', 'There was an error with the provided QR code.');
    header_redirect('/console/dashboard');
}
elseif ($_SERVER['REQUEST_METHOD'] == 'POST') 
{

    // Basic serverside validation
    if (!validate_blank($_POST['name']) || 
        !validate_blank($_POST['location']) ||
        !validate_blank($_POST['starts_at']) ||
        !validate_blank($_POST['ends_at']))
    {
        message_set('Event Error', 'There was an error with the provided event.', 'red');
        header_redirect('/admin/dashboard');
    }
    
    $query = 'UPDATE events SET
        name = "'.addslashes($_POST['name']).'",
        description = "'.addslashes($_POST['description']).'",
        location = "'.addslashes($_POST['location']).'",
        registration = "'.addslashes($_POST['registration']).'",
        online = "'.addslashes($_POST['online']).'",
        starts_at = "'.addslashes($_POST['starts_at']).'",
        ends_at = "'.addslashes($_POST['ends_at']).'",
        updated_at = NOW()
        WHERE id = '.$_GET['key'].'
        LIMIT 1';
    mysqli_query($connect, $query);

    message_set('Event Success', 'Event has been successfully updated.');
    header_redirect('/admin/dashboard');
    
}

define('APP_NAME', 'Events');
define('PAGE_TITLE', 'Edit Event');
define('PAGE_SELECTED_SECTION', 'events');
define('PAGE_SELECTED_SUB_PAGE', '/admin/dashboardedit');

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
    Edit Event
</p>

<hr>

<h2>Edit Event: <?=$record['name']?></h2>

<!-- Edit form -->
<form
    method="post"
    novalidate
    id="main-form"
>

    <input  
        name="name" 
        class="w3-input w3-border" 
        type="text" 
        id="name" 
        autocomplete="off"
        value="<?=$record['name']?>"
    />
    <label for="name" class="w3-text-gray">
        Name <span id="name-error" class="w3-text-red"></span>
    </label>

    <textarea  
        name="description" 
        class="w3-input w3-border w3-margin-top" 
        id="description" 
        autocomplete="off"
    ><?=$record['description']?></textarea>
    <label for="name" class="w3-text-gray">
        Description <span id="description-error" class="w3-text-red"></span>
    </label>

    <input  
        name="location" 
        class="w3-input w3-border w3-margin-top" 
        type="text" 
        id="location" 
        autocomplete="off"
        value="<?=$record['location']?>"
    />
    <label for="location" class="w3-text-gray">
        Location <span id="location-error" class="w3-text-red"></span>
    </label>

    <input  
        name="registration" 
        class="w3-input w3-border w3-margin-top" 
        type="text" 
        id="registration" 
        autocomplete="off"
        value="<?=$record['registration']?>"
    />
    <label for="registration" class="w3-text-gray">
        Registration URL <span id="registration-error" class="w3-text-red"></span>
    </label>

    <input  
        name="online" 
        class="w3-input w3-border w3-margin-top" 
        type="text" 
        id="online" 
        autocomplete="off"
        value="<?=$record['online']?>"
    />
    <label for="online" class="w3-text-gray">
        Online URL <span id="online-error" class="w3-text-red"></span>
    </label>

    <input  
        name="starts_at" 
        class="w3-input w3-border w3-margin-top" 
        type="datetime-local" 
        id="starts-at" 
        autocomplete="off"
        value="<?=$record['starts_at']?>"
    />
    <label for="starts_at" class="w3-text-gray">
        Start Date <span id="starts-at-error" class="w3-text-red"></span>
    </label>

    <input  
        name="ends_at" 
        class="w3-input w3-border w3-margin-top" 
        type="datetime-local" 
        id="ends-at" 
        autocomplete="off"
        value="<?=$record['ends_at']?>"
    />
    <label for="ends_at" class="w3-text-gray">
        End Date <span id="ends-at-error" class="w3-text-red"></span>
    </label>

    <button class="w3-block w3-btn w3-orange w3-text-white w3-margin-top" onclick="return validateMainForm();">
        <i class="fa-solid fa-tag fa-padding-right"></i>
        Edit Event
    </button>

</form>

<script>

    function validateMainForm() {
        let errors = 0;

        let name = document.getElementById("name");
        let name_error = document.getElementById("name-error");
        name_error.innerHTML = "";
        if (name.value == "") {
            name_error.innerHTML = "(name is required)";
            errors++;
        }

        let location = document.getElementById("location");
        let location_error = document.getElementById("location-error");
        location_error.innerHTML = "";
        if (location.value == "") {
            location_error.innerHTML = "(Location is required)";
            errors++;
        }

        let starts_at = document.getElementById("starts-at");
        let starts_at_error = document.getElementById("starts-at-error");
        starts_at_error.innerHTML = "";
        if (starts_at.value == "") {
            starts_at_error.innerHTML = "(Start date is required)";
            errors++;
        }

        let ends_at = document.getElementById("ends-at");
        let ends_at_error = document.getElementById("ends-at-error");
        ends_at_error.innerHTML = "";
        if (ends_at.value == "") {
            ends_at_error.innerHTML = "(End date is required)";
            errors++;
        }

        if (errors) return false;
    }

</script>

<?php

include('../templates/main_footer.php');
include('../templates/html_footer.php');

?>
