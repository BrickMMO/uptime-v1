<?php

$navigation = navigation_array();

?>

<!-- SIDE NAVIGATION -->

<script>

    window.onload = (event) => {
        let sidebar = document.getElementById("sidebar");
        let overlay = document.getElementById("sidebarOverlay");

        let width = sidebar.getBoundingClientRect().width;

        sidebar.style.left = "-" + width + "px";
        overlay.style.display = "none";
        overlay.style.opacity = "0";
    };

    function w3SidebarToggle(event) {
        let sidebar = document.getElementById("sidebar");
        let overlay = document.getElementById("sidebarOverlay");
        let width = sidebar.getBoundingClientRect().width;

        if (sidebar.style.left == "0px") {
            sidebar.style.transition = "0.5s";
            sidebar.style.left = "-" + width + "px";

            overlay.style.transition = "0.5s";
            overlay.style.opacity = "0";

            setTimeout(function () {
            overlay.style.display = "none";
            w3SidebarCloseAll();
            }, 500);
        } else {
            sidebar.style.transition = "0.5s";
            sidebar.style.left = "0px";

            overlay.style.display = "block";

            setTimeout(function () {
            overlay.style.transition = "0.5s";
            overlay.style.opacity = "1";
            }, 0);

            closeAvatarOptions();
        }

        if(event)
        {
            event.preventDefault();
            event.stopPropagation();
        }
    }
    
</script>

<script>

    function prevent(event)
    {
        event.stopPropagation();
    }

    function w3ToggleSub(event, id) {
        let target = document.getElementById(id);
        let link = target.previousElementSibling;
        let icon = link.getElementsByTagName("i")[0];

        if (target.style.display == "block") {
            icon.classList.remove("fa-caret-down");
            icon.classList.add("fa-caret-right");
            target.style.display = "none";
        } else {
            w3SidebarCloseAll();
            icon.classList.remove("fa-caret-right");
            icon.classList.add("fa-caret-down");
            target.style.display = "block";
        }

        prevent(event);
        return false;
    }

    function w3SidebarCloseAll() {
        let down = document.querySelectorAll("#sidebar .fa-caret-down");
        for (let i = 0; i < down.length; i++) {
            down[i].classList.add("fa-caret-right");
            down[i].classList.remove("fa-caret-down");
            down[i].parentElement.nextElementSibling.style.display = "none";
        }
    }
    
</script>

<nav
    class="w3-sidebar w3-bar-block w3-border-right"
    id="sidebar"
    style="
      width: 100%;
      max-width: 250px;
      left: -250px;
      z-index: 109;
      top: 0px;
      padding-top: 58px;
    "
    onclick="prevent(event);"
>
  <div class="w3-padding-16 w3-border-bottom">
    <a href="<?=ENV_DOMAIN?>/console/dashboard" class="w3-bar-item w3-button">
      <i class="fa-solid fa-gauge fa-padding-right w3-text-dark-grey"></i>
      Dashboard
    </a>
  </div>

  <div class="w3-padding-16 w3-border-bottom">

    <?php foreach($navigation as $level): ?>

      <?php if($level['title'] == 'Administration' && $_user['admin'] != 1): ?>

      <?php else: ?>

        <div class="w3-bar-item w3-text-gray bm-caps"><?=$level['title']?></div>
          
        <?php foreach($level['sections'] as $section): ?>
          <a
            class="w3-bar-item w3-button w3-text-red"
            href="#"
            onclick="w3ToggleSub(event, '<?=$section['id']?>')"
          >
            <?=$section['title']?>
            <i class="fa-solid fa-caret-<?php if(PAGE_SELECTED_SECTION == $section['id']): ?>down<?php else: ?>right<?php endif; ?> w3-text-dark-grey fa-padding-left"></i>
          </a>
          <div id="<?=$section['id']?>" style="display: <?php if(PAGE_SELECTED_SECTION == $section['id']): ?>block<?php else: ?>none<?php endif; ?>">
            <?php foreach($section['pages'] as $page): ?>
              <a href="<?=ENV_DOMAIN?><?=$page['url']?>" class="w3-bar-item w3-button<?php if(PAGE_SELECTED_SUB_PAGE == $page['url']): ?> bm-selected<?php endif; ?>" onclick="prevent(event);">
                <i class="<?=$page['icon']?> fa-padding-right w3-text-dark-grey"></i>
                <?=$page['title']?>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>

      <?php endif; ?>

    <?php endforeach; ?>
        
  </div>
</nav>

<div
    class="w3-overlay"
    style="z-index: 100; display: none; background: rgba(0, 0, 0, 0.4)"
    id="sidebarOverlay"
></div>