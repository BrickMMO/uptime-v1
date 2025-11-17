<?php

function date_now($format = 'MYSQL')
{

    return date_to_format(time(), $format);

}

function date_to_format($date, $format = 'MYSQL')
{

    if(!is_numeric($date)) $date = strtotime($date);

    if($format == 'MYSQL') return date('Y-m-d H:i:s', $date);
    if($format == 'FULL') return date('l F jS Y, g:i a', $date);
    if($format == 'SHORT_FULL') return date('D M j, Y g:i a', $date);
    if($format == 'SHORT') return date('F jS Y, g:i a', $date);
    
}
