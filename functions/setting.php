<?php

function setting_fetch($name, $format = 'plain')
{

    global $connect;

    $query = 'SELECT value
        FROM settings
        WHERE name = "'.$name.'"
        LIMIT 1';
    $result = mysqli_query($connect, $query);
    $record = mysqli_fetch_assoc($result);
    
    switch($format)
    {
        case 'comma':
            $record['value'] = str_replace(array(', ', ','), ', ', $record['value']);
            return $record['value'];
        case 'comma_2_array':
            $record['value'] = explode(',', $record['value']);
            $record['value'] = array_filter(array_map('trim', $record['value']));
            return $record['value'];
        default:
            return $record['value'];
    }

}

function setting_update($name, $value)
{

    global $connect;

    $query = 'UPDATE settings SET
        value = "'.addslashes($value).'"
        WHERE name = "'.$name.'"
        LIMIT 1';
    mysqli_query($connect, $query);

}

function setting_increment($name, $value)
{

    global $connect;

    $query = 'UPDATE settings SET
        value = value + '.$value.'
        WHERE name = "'.$name.'"
        LIMIT 1';
    mysqli_query($connect, $query);

}
