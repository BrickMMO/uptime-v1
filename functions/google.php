<?php

use Google\Client;
use Google\Service\Drive;
use Google\Service\Gmail;

function google_display_token($token)
{
    return substr(str_repeat('X', strlen($token)), 0, 30);
}

function google_revoke($access_token)
{

    

}

function google_list_files($access_token, $folder_name, $folder_id, $files = array())
{

    $output = false;

    $client = google_get_client($access_token);
    $service = new Drive($client);

    $optParams = array(
        'q' => "'$folder_id' in parents",
        'pageSize' => 100,
        'fields' => 'nextPageToken, files(id, name, mimeType)',
    );

    $results = $service->files->listFiles($optParams);

    if(count($results->getFiles()) == 0)
    {
        
        return $files;

    } 
    else 
    {

        if($output)
        {
            echo '<strong>Files in folder: '.$folder_name.':</strong>
                <br>
                <div style="padding:10px">';
        }

        foreach($results->getFiles() as $file) 
        {

            if($output) echo '<div style="padding:10px">';

            // printf("%s (%s) - %s<br>", $file->getName(), $file->getId(), $file->getMimeType());
            // application/vnd.google-apps.folder

            $new_file = array(
                'name' => $file->getName(),
                'google_id' => $file->getId(),
                'folder' => $folder_name,
            );

            if(in_array($file->getMimeType(), array('image/png', 'image/jpeg', 'image/gif')))
            {

                $new_file['type'] = 'image';

                if($output)
                {
                    printf("%s (%s) - %s<br>", $file->getName(), $file->getId(), $file->getMimeType());
                    // https://lh3.googleusercontent.com/drive-storage/AJQWtBMd5ZruP6Vb06d-FqkdgR4RPQ1IWTmBXbSvABS5BF7oatvvQH5m3Xm_tPwSKa64dhTjwEF27i8Z0JArLoS8U0IBl61tmULKJFOaWtZ8dKHlq38=s512
                    // echo $file->getImageServingUrl();
                    // $file2 = $service->files->get($file->getId(), array('alt' => 'media'));
                    // echo '<img src="data:image/jpeg;base64, '.base64_encode($file2->getBody()).'">';
                    // echo 'Thumb: '.$file->getBody();
                    // echo $file->getThumbnailLink();
                    echo 'https://lh3.googleusercontent.com/d/'.$file->getId();
                    // echo '<img src="https://lh3.googleusercontent.com/d/'.$file->getId().'=s100">';
                    // echo '<img src="http://lhx.ggpht.com/'.$file->getId().'=s100">';
                    // debug_pre($file);
                    // die();
                }

            }
            elseif(in_array($file->getMimeType(), array('video/mp4')))
            {

                $new_file['type'] = 'video';

                if($output)
                {
                    printf("%s (%s) - %s<br>", $file->getName(), $file->getId(), $file->getMimeType());
                    // $file2 = $service->files->get($file->getId(), array('alt' => 'media'));
                    // echo 'https://lh3.googleusercontent.com/d/1Ke1uMMvAbPVzlM9kPKLNZ-lU799yojJy=s100';
                    // echo '<br>';
                    // echo 'https://lh3.googleusercontent.com/drive-storage/'.$file->getId().'=s512';
                    // echo '<br>';
                    echo 'https://drive.google.com/file/d/'.$file->getId().'/view';
                    echo '<br>';
                    echo 'https://drive.google.com/file/d/'.$file->getId().'/preview';
                    echo '<br>';
                    echo 'https://drive.usercontent.google.com/download?id='.$file->getId().'&export=download&authuser=0';
                    // die();
                }

            }
            elseif(in_array($file->getMimeType(), array('audio/x-wav')))
            {

                $new_file['type'] = 'audio';

                if($output)
                {
                    printf("%s (%s) - %s<br>", $file->getName(), $file->getId(), $file->getMimeType());
                    echo 'https://drive.google.com/file/d/'.$file->getId().'/view';
                    echo '<br>';
                    echo 'https://drive.google.com/file/d/'.$file->getId().'/preview';
                    echo '<br>';
                    echo 'https://drive.usercontent.google.com/download?id='.$file->getId().'&export=download&authuser=0';
                    // die();
                }

            }

            if($file->getMimeType() == 'application/vnd.google-apps.folder')
            {

                $files = google_list_files($access_token, $file->getName(), $file->getId(), $files);

            }
            else
            {

                $files[] = $new_file;

            }

            if($output) echo '</div>';    

        }

        if($output) echo '</div>';

    }

    return $files;

}

function google_get_client($access_token = false)
{

    $client = new Client();
    // $client->setApplicationName('Google Drive API PHP Quickstart');
    $client->setApplicationName('BrickMMO');
    // $client->setScopes(Drive::DRIVE_METADATA_READONLY,Gmail::GMAIL_READONLY);
    $client->setScopes(Drive::DRIVE,Gmail::GMAIL_READONLY);
    $client->setAuthConfig('../credentials.json');
    $client->setAccessType('offline');
    // $client->setRedirectUri('http://localhost:8888/callback.php');
    // $client->setRedirectUri(ENV_DOMAIN.'/action/google/app/token');
    $client->setRedirectUri('https://account.local.brickmmo.com:33/action/google/app/token');

    /**
     * If access token is provided, use it to initiate the client.
     */
    if ($access_token) 
    {
        $client->setAccessToken($access_token);
        $_SESSION['access_token'] = $access_token;
    }

    /**
     * If access token is in the session, this page load is fresh off a Google authtication
     * callback, and use the session token.
     */
    elseif(isset($_SESSION['access_token']))
    {
        $client->setAccessToken($_SESSION['access_token']['access_token']);
    }

    return $client;

}

function google_auth_url()
{

    $client = google_get_client();
    $auth_url = $client->createAuthUrl();
    return $auth_url;

}