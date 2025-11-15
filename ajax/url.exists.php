<?php

if(!isset($_POST['url']))
{
    header_bad_request();
    $data = array('message'=>'Missing Parameter.', 'error' => true);
    return;
}

// Normalize the input URL
$normalized_url = string_normalize_url($_POST['url']);

// Get all existing URLs and normalize them for comparison
$query = 'SELECT id, url FROM assets';
if(isset($_POST['id'])) $query .= ' WHERE id != '.intval($_POST['id']);
$result = mysqli_query($connect, $query);

$url_exists = false;
while($row = mysqli_fetch_assoc($result))
{
    if(string_normalize_url($row['url']) === $normalized_url)
    {
        $url_exists = true;
        break;
    }
}

if($url_exists)
{
    $data = array('message' => 'URL exists.', 'error' => true);
}
else
{
    $data = array('message' => 'URL does not exist.', 'error' => false);
}
