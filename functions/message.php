<?php

function message_set($title, $text, $colour = 'green', $overwrite = false, $icon = 'fa-triangle-exclamation')
{
    if(isset($_SESSION['message']) && !$overwrite) return false;
    
    $_SESSION['message']['title'] = $title;
    $_SESSION['message']['text'] = $text;
    $_SESSION['message']['colour'] = $colour;
    $_SESSION['message']['icon'] = $icon;
}
