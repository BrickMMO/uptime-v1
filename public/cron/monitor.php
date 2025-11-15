<?php

include('../../includes/connect.php');
include('../../includes/config.php');

echo '<h1>Uptime Monitor</h1>';
echo '<p>Started: '.date('Y-m-d H:i:s').'</p>';
echo '<hr>';

// Get all active assets
$query = 'SELECT * FROM assets WHERE deleted_at IS NULL ORDER BY name';
$result = mysqli_query($connect, $query);

if(!mysqli_num_rows($result))
{
    echo '<p>No assets to monitor</p>';
    exit();
}

$total_assets = mysqli_num_rows($result);
$checked = 0;
$up_count = 0;
$down_count = 0;

echo '<p>Found '.$total_assets.' assets to check</p>';
echo '<hr>';

while($asset = mysqli_fetch_assoc($result))
{
    $checked++;
    
    echo '<h2>['.$checked.'/'.$total_assets.'] '.$asset['name'].'</h2>';
    echo '<p>URL: <a href="'.$asset['url'].'" target="_blank">'.$asset['url'].'</a></p>';
    
    // Initialize variables
    $status = 0;
    $response_time = 0;
    $status_code = 0;
    $error_message = null;
    
    // Start timing
    $start_time = microtime(true);
    
    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $asset['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'BrickMMO Uptime Monitor v1.0');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    
    // Execute request
    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    // End timing
    $end_time = microtime(true);
    $response_time = round(($end_time - $start_time) * 1000, 2);
    
    // Determine status
    if($curl_error)
    {
        $status = 0;
        $error_message = $curl_error;
        $down_count++;
        echo '<p style="color: red;">❌ DOWN - '.$curl_error.'</p>';
    }
    elseif($status_code >= 200 && $status_code < 400)
    {
        $status = 1;
        $up_count++;
        echo '<p style="color: green;">✅ UP - HTTP '.$status_code.'</p>';
    }
    else
    {
        $status = 0;
        $error_message = 'HTTP '.$status_code;
        $down_count++;
        echo '<p style="color: red;">❌ DOWN - HTTP '.$status_code.'</p>';
    }
    
    echo '<p>Response Time: '.$response_time.'ms</p>';
    
    // Record check to database
    $query = 'INSERT INTO checks SET
        asset_id = '.$asset['id'].',
        up = '.$status.',
        response_time = '.$response_time.',
        status_code = '.$status_code.',
        error_message = '.($error_message ? '"'.mysqli_real_escape_string($connect, $error_message).'"' : 'NULL').',
        checked_at = NOW()';
    
    if(mysqli_query($connect, $query))
    {
        echo '<p>✓ Recorded to database</p>';
    }
    else
    {
        echo '<p style="color: red;">✗ Database error: '.mysqli_error($connect).'</p>';
    }
    
    // Check if asset needs a screenshot
    if(empty($asset['image']) && $status == 1)
    {
        echo '<p>📸 Capturing screenshot...</p>';
        
        $screenshot_url = 'https://pdfer.codeadam.ca/url-to-image?url='.urlencode($asset['url']).'&width=384&height=216';
        echo '<p>API: <a href="'.$screenshot_url.'" target="_blank">'.$screenshot_url.'</a></p>';
        
        $screenshot_data = @file_get_contents($screenshot_url);
        
        if($screenshot_data !== false)
        {
            $base64_image = base64_encode($screenshot_data);
            $data_uri = 'data:image/png;base64,'.$base64_image;
            
            $update_query = 'UPDATE assets SET
                image = "'.mysqli_real_escape_string($connect, $data_uri).'"
                WHERE id = '.$asset['id'].'
                LIMIT 1';
            
            if(mysqli_query($connect, $update_query))
            {
                echo '<p>✓ Screenshot saved</p>';
            }
            else
            {
                echo '<p style="color: orange;">✗ Failed to save screenshot: '.mysqli_error($connect).'</p>';
            }
        }
        else
        {
            echo '<p style="color: orange;">✗ Failed to capture screenshot</p>';
        }
    }
    
    echo '<hr>';
    
    // Small delay to avoid hammering servers
    usleep(500000); // 0.5 seconds

}

// Clean up old checks (older than 30 days)
$query = 'DELETE FROM checks WHERE checked_at < DATE_SUB(NOW(), INTERVAL 30 DAY)';
mysqli_query($connect, $query);
$deleted = mysqli_affected_rows($connect);

echo '<h2>Summary</h2>';
echo '<p>Total Assets: '.$total_assets.'</p>';
echo '<p>Up: '.$up_count.'</p>';
echo '<p>Down: '.$down_count.'</p>';
echo '<p>Old Records Deleted: '.$deleted.'</p>';
echo '<p>Completed: '.date('Y-m-d H:i:s').'</p>';
