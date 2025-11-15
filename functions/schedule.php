<?php

function schedule_fetch($identifier)
{

    if(!$identifier) return false;

    global $connect;

    $query = 'SELECT *
        FROM schedules
        WHERE id = "'.addslashes($identifier).'"
        LIMIT 1';
    $result = mysqli_query($connect, $query);

    if(mysqli_num_rows($result)) return mysqli_fetch_assoc($result);
    else return false;

}

function schedule_type_fetch($id)
{

    if(!$id) return false;

    global $connect;

    $query = 'SELECT *
        FROM schedule_types
        WHERE id = "'.addslashes($id).'"
        LIMIT 1';
    $result = mysqli_query($connect, $query);

    if(mysqli_num_rows($result)) return mysqli_fetch_assoc($result);
    else return false;

}

function schedule_log_fetch($id)
{
    if(!$id) return false;

    global $connect;

    $query = 'SELECT schedule_logs.*,
        schedules.host_id,
        hosts.voice AS voice
        FROM schedule_logs
        LEFT JOIN schedules ON schedules.id = schedule_logs.schedule_id
        LEFT JOIN hosts ON hosts.id = schedules.host_id
        WHERE schedule_logs.id = "'.addslashes($id).'"
        LIMIT 1';
    $result = mysqli_query($connect, $query);

    if(mysqli_num_rows($result)) return mysqli_fetch_assoc($result);
    else return false;
}

function schedule_length($id)
{

    global $connect;

    $schedule = schedule_fetch($id);

    $query = 'SELECT *
        FROM schedules
        WHERE minute > "'.$schedule['minute'].'"
        AND city_id = "'.$schedule['city_id'].'"
        ORDER BY minute ASC
        LIMIT 1';
    $result = mysqli_query($connect, $query);

    if(!mysqli_num_rows($result))
    {
        $length = 15 - $schedule['minute'];
    }
    else
    {
        $schedule_next = mysqli_fetch_assoc($result);
        $length = $schedule_next['minute'] - $schedule['minute'];
    }

    return $length;

}