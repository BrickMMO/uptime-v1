<?php if(PAGE_SELECTED_SUB_PAGE): ?>

    <?php

    $navigation = navigation_array(PAGE_SELECTED_SUB_PAGE);

    ?>

    <!-- SUB NAV -->

    <nav
        class="w3-sidebar w3-bar-block w3-border-right"
        id="sidebar-sub"
        style="
        width: 100%;
        max-width: 240px;
        z-index: 108;
        top: 0px;
        padding-top: 58px;
        "
    >
        <div class="w3-padding-16 w3-border-bottom">
            <div class="w3-bar-item w3-text-gray bm-caps">
                <i class="<?=$navigation['icon']?> fa-padding-right"></i> 
                <?php if(isset($navigation['title'])): ?>
                    <?=$navigation['title']?>
                <?php endif; ?>
            </div>
        </div>

        <div class="w3-padding-16 w3-border-bottom">
        
            <?php foreach($navigation['sub-pages'] as $page): ?>

                <?php if(isset($page['title'])): ?>

                    <a
                        class="w3-bar-item w3-button w3-text-<?=$page['colour']?> <?php if($page['url'] == PAGE_SELECTED_SUB_PAGE): ?>bm-selected<?php endif; ?>"
                        href="<?=(strpos($page['url'], 'http') === 0) ? '' : ENV_DOMAIN?><?=$page['url']?>"
                    >
                        <?php if(isset($page['icon'])): ?>
                            <?php if(substr($page['icon'], 0, 2) == 'fa'): ?>
                                <i class="<?=$page['icon']?> fa-padding-right"></i> 
                            <?php elseif(substr($page['icon'], 0, 2) == 'bm'): ?>
                                <i class="<?=$page['icon']?> fa-padding-right"></i> 
                            <?php endif; ?>
                        <?php endif; ?> 
                        <?=$page['title']?>   
                    </a>

                <?php else: ?>    

                    </div>
                    <div class="w3-padding-16 w3-border-bottom">

                <?php endif; ?>

            <?php endforeach; ?>
            
        </div>
    </nav>

<?php endif; ?>
