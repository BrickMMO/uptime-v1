
<?php if(isset($_SESSION['message'])): ?>

    <div class="w3-panel w3-<?=$_SESSION['message']['colour']?>">
        <h3 class="w3-margin-top"><i class="fa-solid <?=$_SESSION['message']['icon']?>"></i> <?=$_SESSION['message']['title']?></h3>
        <p><?=$_SESSION['message']['text']?></p>
    </div>

    <?php unset($_SESSION['message']); ?>

<?php endif; ?>
