<?php

function stores_image($id)
{
    $store = stores_fetch($id);
    return $store['image'] ? $store['image'] : '/images/no_city.png';
}

function stores_fetch($identifier, $field = false)
{

    if(!$identifier) return false;

    global $connect;

    if($field)
    {
        $query = 'SELECT *
            FROM stores
            WHERE '.$field.' = "'.addslashes($identifier).'"
            LIMIT 1';
    }
    else
    {
        $query = 'SELECT *
            FROM stores
            WHERE id = "'.addslashes($identifier).'"
            LIMIT 1';
    }
    
    $result = mysqli_query($connect, $query);

    if(mysqli_num_rows($result)) return mysqli_fetch_assoc($result);
    else return false;

}