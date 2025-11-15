<?php

header_not_found();

define('APP_NAME', 'Console');

define('PAGE_TITLE', 'Page Not Found');
define('PAGE_SELECTED_SECTION', '');
define('PAGE_SELECTED_SUB_PAGE', '');

include('../templates/html_header.php');
include('../templates/login_header.php');  

?>

<div class="w3-center">

    <h1>404 Error</h1>

    <?php include('../templates/message.php'); ?>

    <a href="<?=ENV_DOMAIN?>/">Uptime Stats</a> | 

    <?php if($_user): ?>
        <a href="<?=ENV_SSO_DOMAIN?>/action/logout">Logout</a> | 
        <a href="<?=ENV_SSO_DOMAIN?>/dashboard">Account Dashboard</a>
    <?php else: ?>
        <a href="<?=ENV_SSO_DOMAIN?>/login">Login</a> | 
        <a href="<?=ENV_SSO_DOMAIN?>/register">Register</a>
    <?php endif; ?>

</div>

<?php

include('../templates/main_footer.php');
include('../templates/debug.php');
include('../templates/html_footer.php');
