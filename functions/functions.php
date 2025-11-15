<?php

foreach (scandir(__DIR__) as $file) 
{
    if ($file !== '.' && $file !== '..' && $file != 'functions.php') 
    {
        include(__DIR__.'/'.$file);
    }
}
