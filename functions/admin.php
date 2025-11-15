<?php

function admin_check()
{

    global $_user;

    if($_user['admin'] != 1)
    {
        header_redirect(ENV_DOMAIN.'/');
    }

}