<?php

function invite_fetch($identifier)
{

    global $connect;

    $query = 'SELECT *
        FROM invites
        WHERE invite_hash = "'.addslashes($identifier).'"
        LIMIT 1';
    $result = mysqli_query($connect, $query);

    if(mysqli_num_rows($result)) return mysqli_fetch_assoc($result);
    else return false;

}