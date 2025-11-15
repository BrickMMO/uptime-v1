<?php

// Fetch panel data based on city_id
function get_segments_data_by_schedule_5()
{
    global $connect;
    $query = "SELECT s.time, sg.name AS title
              FROM Schedules s
              JOIN Segments sg ON s.segment_id = sg.id
              ORDER BY s.time ASC LIMIT 5";

    $result = mysqli_query($connect, $query);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    return $data;
}

// Fetch panel data based on city_id
function get_broadcast_logs()
{
    global $connect;
    $query = "SELECT content FROM `broadcast_logs`";

    $result = mysqli_query($connect, $query);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    return $data;
}

function get_broadcast_list()
{
    global $connect;
    $query = "SELECT s.id,s.segment_id,s.time, sg.name AS title 
              FROM Schedules s 
              JOIN Segments sg ON s.segment_id = sg.id 
              ORDER BY s.time ASC";

    $result = mysqli_query($connect, $query);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    return $data;
}

// Function to call ChatGPT API
function generateContent($segmentId)
{

    // die('generateContent');

    global $connect;

    $query = "SELECT name FROM Segments WHERE id = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 'i', $segmentId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $segmentName = $row ? $row['name'] : 'Unknown Segment';

    $apiKey = OPENAI_SECRET;
    $data = [
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'system', 'content' => "Write a detailed script"],
            ['role' => 'user', 'content' => "Write a detailed, engaging LEGO® based script for a 5-minute radio segment on " . $segmentName]
        ],
        'max_tokens' => 1000,
        'temperature' => 0,
        "top_p" => 0,
        "frequency_penalty" => 0,
        "presence_penalty" => 0,
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $apiKey, 'Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);


    /*
    $apiKey = OPENAI_SECRET;
    $data = [
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'system', 'content' => "Write a detailed script"],
            ['role' => 'user', 'content' => "Write a detailed, engaging LEGO® based script for a 5-minute radio segment on " . $segmentName]
        ],
        'max_tokens' => 1000,
        'temperature' => 0,
        "top_p" => 0,
        "frequency_penalty" => 0,
        "presence_penalty" => 0,
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $apiKey, 'Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    debug_pre($response);
    */
    
    return $result['choices'][0]['message']['content'] ?? 'Default content due to API failure.';
}

function radio_script($log_id, $city_id)
{

    global $connect;

    $log = schedule_log_fetch($log_id);
    $schedule = schedule_fetch($log['schedule_id']);
    $schedule_type = schedule_type_fetch($schedule['type_id']);
    $length = schedule_length($schedule['id']);

    // $schedule_type['filename'] = 'city.php';
    // $schedule_type['filename'] = 'traffic.php';

    require('../applications/radio_prompts/'.$schedule_type['filename']);

    // debug_pre($prompt);

    $data = [
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'system', 'content' => 'Write a detailed script'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'max_tokens' => 1000,
        'temperature' => 0,
        "top_p" => 0,
        "frequency_penalty" => 0,
        "presence_penalty" => 0,
    ];

    $headers = [
        'Authorization: Bearer '.OPENAI_SECRET,
        'Content-Type: application/json',
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
    $response = curl_exec($ch);
    curl_close($ch);

    $query = 'UPDATE schedule_logs SET
        script = "'.addslashes($response).'"
        WHERE id = "'.$log_id.'"
        LIMIT 1';
    mysqli_query($connect, $query);

}

function radio_mp3($log_id)
{

    global $connect;

    $log = schedule_log_fetch($log_id);
    // $voice = schedule_log_fetch($log['voice']);
    // debug_pre($log);
    // echo gettype($log['script']);

    // echo gettype($log['voice']);
    // string - correct
 
    // ash - correct
    // debug_pre($voice);


    $script = json_decode($log['script'], true);
    
    // echo gettype($script);
    // debug_pre($script);


    $data = [
        "model" => "tts-1",
        "input" => $script['choices'][0]['message']['content'],
        "voice" => $log['voice'],
    ];

    debug_pre($data);

    // https://platform.openai.com/docs/guides/text-to-speech
    $headers = [
        'Authorization: Bearer '.OPENAI_SECRET,
        'Content-Type: application/json',
    ];

    $ch = curl_init('https://api.openai.com/v1/audio/speech');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
    $response = curl_exec($ch);
    curl_close($ch);

    $log_filename = $log_id.'.mp3';

    if(!file_exists('../public/radio_queue/'))
    {
        mkdir('../public/radio_queue/');
    }

    $log_folder = '../public/radio_queue/';
    $log_file = $myfile = fopen($log_folder.$log_filename, "w");
    
    fwrite($log_file, $response);

}


function radio_length($filename) {

    global $connect;

    $query = sprintf("SELECT `length` 
        FROM `schedule_types` 
        WHERE `filename` = '%s'", $filename);

    $result = mysqli_query($connect, $query);
    $length = mysqli_fetch_assoc($result);

    return $length['length'];
}
