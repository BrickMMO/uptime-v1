<?php

function host_fetch($identifier)
{

    if(!$identifier) return false;

    global $connect;

    $query = 'SELECT *
        FROM hosts
        WHERE id = "'.addslashes($identifier).'"
        LIMIT 1';
    $result = mysqli_query($connect, $query);

    if(mysqli_num_rows($result)) return mysqli_fetch_assoc($result);
    else return false;
}


function host_prompt($city_id, $file) {

    global $connect;
    
// get host
$query='SELECT schedules.*,
    schedule_types.filename AS type_filename,
    hosts.name AS host_name,
    hosts.prompt AS host_prompt
    FROM schedules
    LEFT JOIN schedule_types
    ON schedules.type_id = schedule_types.id
    LEFT JOIN hosts
    ON schedules.host_id = hosts.id
    WHERE schedules.city_id = "'.$city_id.'"
    AND schedule_types.filename = "' . $file . '"
    LIMIT 1';

$result = mysqli_query($connect, $query);
debug_pre($result);

if (mysqli_num_rows($result) > 0) {
    $hosts = mysqli_fetch_assoc($result);
    debug_pre($hosts);

    $prompt = ' The host is ' . $hosts['host_name'] . ', who has the following personal traits: ' . $hosts['host_prompt'] . '.';
    return $prompt;
} else {
    // If no results, return default host
    return 'Hello, Lively Radio listeners! It is your girl Flora here, bringing you the buzz from our vibrant city!';
}

}







