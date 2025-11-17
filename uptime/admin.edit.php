<?php

security_check();
admin_check();

if(
    !isset($_GET['key']) || 
    !is_numeric($_GET['key']))
{
    message_set('Asset Error', 'There was an error with the provided asset.');
    header_redirect('/admin/dashboard');
}

$query = 'SELECT *
    FROM assets
    WHERE id = '.$_GET['key'].'
    LIMIT 1';
$result = mysqli_query($connect, $query);

if(!mysqli_num_rows($result))
{
    message_set('Asset Error', 'There was an error with the provided asset.');
    header_redirect('/admin/dashboard');
}

$record = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{

    // Basic serverside validation
    if (!validate_blank($_POST['name']) || 
        !validate_blank($_POST['url']))
    {
        message_set('Asset Error', 'There was an error with the provided asset.', 'red');
        header_redirect('/admin/edit/'.$_GET['key']);
    }

    // Validate URL format
    if (!filter_var($_POST['url'], FILTER_VALIDATE_URL))
    {
        message_set('Asset Error', 'Please provide a valid URL.', 'red');
        header_redirect('/admin/edit/'.$_GET['key']);
    }

    $query = 'UPDATE assets SET
        name = "'.addslashes($_POST['name']).'",
        url = "'.addslashes($_POST['url']).'",
        updated_at = NOW()
        WHERE id = '.$_GET['key'].'
        LIMIT 1';
    mysqli_query($connect, $query);

    message_set('Asset Success', 'Asset has been successfully updated.');
    header_redirect('/admin/dashboard');
    
}

define('APP_NAME', 'Uptime');
define('PAGE_TITLE', 'Edit Asset');
define('PAGE_SELECTED_SECTION', 'admin-dashboard');
define('PAGE_SELECTED_SUB_PAGE', '/admin/edit');

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
    <a href="/admin/dashboard">Uptime</a> / 
    Edit Asset
</p>

<hr>

<h2>Edit Asset</h2>

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

    <input  
        name="url" 
        class="w3-input w3-border w3-margin-top" 
        type="url" 
        id="url" 
        autocomplete="off"
        placeholder="https://example.com"
        value="<?=$record['url']?>"
    />
    <label for="url" class="w3-text-gray">
        URL <span id="url-error" class="w3-text-red"></span>
    </label>

    <button type="button" class="w3-block w3-btn w3-orange w3-text-white w3-margin-top" onclick="validateMainForm();">
        <i class="fa-solid fa-tag fa-padding-right"></i>
        Update Asset
    </button>

</form>

<script>

    async function validateMainForm() {
        let errors = 0;

        let name = document.getElementById("name");
        let name_error = document.getElementById("name-error");
        name_error.innerHTML = "";
        if (name.value == "") {
            name_error.innerHTML = "(Name is required)";
            errors++;
        }

        let url = document.getElementById("url");
        let url_error = document.getElementById("url-error");
        url_error.innerHTML = "";
        if (url.value == "") {
            url_error.innerHTML = "(URL is required)";
            errors++;
        } else {
            const json = await validateExistingUrl(url.value, <?=$record['id']?>);
            if(json.error == true)
            {
                url_error.innerHTML = "(URL already exists)";
                errors++;
            }
        }

        if (errors) return false;

        let mainForm = document.getElementById('main-form');
        mainForm.submit();
    }

    async function validateExistingUrl(url, id) {
        return fetch('/ajax/url/exists',{
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({url: url, id: id})
            })  
            .then((response)=>response.json())
            .then((responseJson)=>{return responseJson});
    }

</script>

<?php

include('../templates/main_footer.php');
include('../templates/debug.php');
include('../templates/html_footer.php');
