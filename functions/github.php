<?php

function github_display_token($token)
{
    return strtoupper('gho_'.str_repeat('X', strlen($token) - 4));
}

function github_url($redirect_uri = '/action/github/user/token')
{

    return 'https://github.com/login/oauth/authorize?scope=read:user,user:email,public_repo&client_id='.GITHUB_CLIENT_ID.
        '&redirect_uri='.urlencode(ENV_DOMAIN.$redirect_uri);

}

function github_revoke($access_token)
{

    $url = 'https://api.github.com/applications/'.GITHUB_CLIENT_ID.'/grant';

    $headers = [
        'Accept: application/vnd.github+json',
        "Authorization: Basic ". base64_encode(GITHUB_CLIENT_ID.':'.GITHUB_CLIENT_SECRET),
        'User-Agent: BrickMMO',
    ];

    $data = [
        'access_token' => $access_token,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $result = curl_exec($ch);

}

function github_access_token($code)
{
    $url = 'https://github.com/login/oauth/access_token';

    $data = [
        'client_id' => GITHUB_CLIENT_ID,
        'client_secret' => GITHUB_CLIENT_SECRET,
        'code' => $code,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    parse_str(curl_exec($ch), $result);
    curl_close($ch);

    return $result;

}

function github_emails($access_token)
{

    $url = "https://api.github.com/user/emails";
    
    $headers = [
        'Accept: application/json',
        'Authorization: Bearer '.$access_token,
        'User-Agent: BrickMMO',
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = json_decode(curl_exec($ch), true);
    curl_close($ch);

    return $result;

}

function github_user($access_token)
{

    $url = "https://api.github.com/user";

    $headers = [
        'Accept: application/json',
        'Authorization: Bearer '.$access_token,
        'User-Agent: BrickMMO',
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = json_decode(curl_exec($ch), true);
    curl_close($ch);

    return $result;

}

function github_scan_repo($account, $repo)
{

    global $connect;

    $query = 'DELETE FROM repos 
        WHERE owner = "'.$account.'"
        AND name = "'.$repo.'"';
    mysqli_query($connect, $query);

    $github = setting_fetch('GITHUB_ACCESS_TOKEN');
    $user = user_fetch($_SESSION['user']['id']);

    // Fetch repo information
    $url = 'https://api.github.com/repos/'.$account.'/'.$repo;

    $headers[] = 'Content-type: application/json';
    $headers[] = 'Authorization: Bearer '.$github;
    $headers[] = 'User-Agent: Awesome-Octocat-App';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $details = json_decode(curl_exec($ch), true);

    curl_close($ch);

    // Fetch README.md information
    $url = 'https://api.github.com/repos/'.$account.'/'.$repo.'/contents/README.md';

    $headers[] = 'Content-type: application/json';
    // $headers[] = 'Authorization: Bearer '.$user['github_access_token'];
    // $headers[] = 'User-Agent: Awesome-Octocat-App';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $readme = json_decode(curl_exec($ch), true);

    curl_close($ch);


    // Fetch favicon.ico information
    $url = 'https://api.github.com/repos/'.$account.'/'.$repo.'/contents/favicon.ico';

    $headers[] = 'Content-type: application/json';
    // $headers[] = 'Authorization: Bearer '.$user['github_access_token'];
    // $headers[] = 'User-Agent: Awesome-Octocat-App';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $favicon = json_decode(curl_exec($ch), true);

    curl_close($ch);


    // Fetch favicon.ico information
    $url = 'https://api.github.com/repos/'.$account.'/'.$repo.'/contents/CNAME';

    $headers[] = 'Content-type: application/json';
    // $headers[] = 'Authorization: Bearer '.$user['github_access_token'];
    // $headers[] = 'User-Agent: Awesome-Octocat-App';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $cname = json_decode(curl_exec($ch), true);

    curl_close($ch);


    // Fetch .gitignore information
    $url = 'https://api.github.com/repos/'.$account.'/'.$repo.'/contents/.gitignore';

    $headers[] = 'Content-type: application/json';
    // $headers[] = 'Authorization: Bearer '.$user['github_access_token'];
    // $headers[] = 'User-Agent: Awesome-Octocat-App';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $gitignore = json_decode(curl_exec($ch), true);

    curl_close($ch);


    // Fetch pulls information
    $url = 'https://api.github.com/repos/'.$account.'/'.$repo.'/pulls';

    $headers[] = 'Content-type: application/json';
    // $headers[] = 'Authorization: Bearer '.$user['github_access_token'];
    // $headers[] = 'User-Agent: Awesome-Octocat-App';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $pulls = json_decode(curl_exec($ch), true);

    curl_close($ch);


    // Fetch favicon.ico information
    $url = 'https://api.github.com/repos/'.$account.'/'.$repo.'/branches/main/protection';

    $headers[] = 'Content-type: application/json';
    // $headers[] = 'Authorization: Bearer '.$user['github_access_token'];
    // $headers[] = 'User-Agent: Awesome-Octocat-App';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $protection = json_decode(curl_exec($ch), true);

    curl_close($ch);


    // debug_pre($protection);
    // die();

    /*
    // Fetch pages information
    $url = 'https://api.github.com/repos/'.$_GET['account'].'/'.$_GET['repo'].'/pages';

    $headers[] = 'Content-type: application/json';
    // $headers[] = 'Authorization: Bearer '.$user['github_access_token'];
    // $headers[] = 'User-Agent: Awesome-Octocat-App';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $pages = json_decode(curl_exec($ch), true);

    curl_close($ch);
    */

    /*
    echo '<hr />';
    echo '<h2>REPO</h2>';
    debug_pre($repo);
    echo '<hr />';
    echo '<h2>README</h2>';
    debug_pre($readme);
    echo '<hr />';
    echo '<h2>FAVICON</h2>';
    debug_pre($favicon);
    echo '<hr />';
    echo '<h2>CNAME</h2>';
    debug_pre($cname);
    echo '<hr />';
    echo '<h2>GITIGNORE</h2>';
    debug_pre($gitignore);
    echo '<hr />';
    echo '<h2>PULLS</h2>';
    debug_pre($pulls);
    echo '<hr />';
    echo '<h2>PROTECTION</h2>';
    debug_pre($protection);
    echo '<hr />';
    */

    $error_comments = array();

    // error_readme_exists: boolean default:0
    $errors['error_readme_exists'] = 1;

    if(!isset($readme['path'])) 
    {
        $errors['error_readme_exists'] = 0;
        $error_comments[] = 'README.md does not exists';
    }

    // error_readme_content: boolean default:0
    $errors['error_readme_contents'] = 1;

    if(isset($readme['path'])) 
    {
        $content = base64_decode($readme['content']);

        // echo '<pre>';
        // echo htmlentities($content);
        // echo '</pre>';

        if(strpos($content, '# ') !== 0)
        {
            $errors['error_readme_contents'] = 0;
            $error_comments[] = 'README.md is missing main heading';
        }

        if(!strpos($content, '## '))
        {
            $errors['error_readme_contents'] = 0;
            $error_comments[] = 'README.md does not appear to have level two headings';
        }

        if(!strpos($content, 'Repo Resources'))
        {
            $errors['error_readme_contents'] = 0;
            $error_comments[] = 'README.md does not appear to have resources';
        }

        if(!strpos($content, 'Project Stack'))
        {
            $errors['error_readme_contents'] = 0;
            $error_comments[] = 'README.md does not appear to have project stack';
        }

        if(!strpos($content, 'codeadam-logo-coloured-horizontal.png') && !strpos($content, 'brickmmo-logo-coloured-horizontal.png'))
        {
            $errors['error_readme_contents'] = 0;
            $error_comments[] = 'README.md does not appear to have a footer image';
        }

        if(strpos($content, 'code-block.png'))
        {
            $errors['error_readme_contents'] = 0;
            $error_comments[] = 'README.md appears to have an outdated footer image';
        }

    }

    // error_favicon_exits: boolean default:0
    $errors['error_favicon_exists'] = 1;

    if($details['has_pages'] && isset($cname['path']))
    {
        if(!isset($favicon['path']))
        {
            $errors['error_favicon_exists'] = 0;
            $error_comments[] = 'Pages is activated, but there is no favicon.ico';
        }
    }

    // error_gitignore_exists: boolean default:0
    $errors['error_gitignore_exists'] = 1;

    if(!isset($gitignore['path'])) 
    {
        $errors['error_gitignore_exists'] = 0;
        $error_comments[] = '.gitignore does not exists';
    }

    // error_gitignore_contents: boolean default:0
    $errors['error_gitignore_contents'] = 1;

    if(isset($gitignore['path'])) 
    {
        $content = base64_decode($gitignore['content']);

        // echo '<pre>';
        // echo htmlentities($content);
        // echo '</pre>';

        if(!is_numeric(strpos($content, '.DS_Store')))
        {
            $errors['error_gitignore_contents'] = 0;
            $error_comments[] = '.gitignore is missing main .DS_Store';
        }
    }

    // error_protected: boolean default:0
    $errors['error_protected'] = 1;

    if(isset($protection['required_pull_request_reviews']['enabled']))
    {
        $errors['error_protected'] = 0;
        $error_comments[] = 'Main branch is not protected';
    }

    // error_description: boolean default:0
    $errors['error_description'] = 1;

    if(!$details['description'])
    {
        $errors['error_description'] = 0;
        $error_comments[] = 'Repo description is empty';
    }

    // error_topics: boolean default:0
    $errors['error_topics'] = 1;

    if(count($details['topics']) == 0) 
    {
        $errors['error_topics'] = 0;
        $error_comments[] = 'Repo has no topics';
    }

    // error_notes: boolean default:0
    // Use of old notes/warning syntax

    // pull_requests: integer
    $pull_requests = count($pulls);

    /*
    debug_pre($errors);
    debug_pre($error_comments);
    */

    $query = 'INSERT INTO repos (
            name,
            owner,
            pull_requests,
            error_readme_exists,
            error_readme_contents,
            error_favicon_exists,
            error_gitignore_exists,
            error_gitignore_contents,
            error_protected,
            error_description,
            error_topics,
            error_comments,
            error_count,
            created_at,
            updated_at
        ) VALUES (
            "'.$repo.'",
            "'.$account.'",
            "'.$pull_requests.'",
            "'.$errors['error_readme_exists'].'",
            "'.$errors['error_readme_contents'].'",
            "'.$errors['error_favicon_exists'].'",
            "'.$errors['error_gitignore_exists'].'",
            "'.$errors['error_gitignore_contents'].'",
            "'.$errors['error_protected'].'",
            "'.$errors['error_description'].'",
            "'.$errors['error_topics'].'",
            "'.implode(chr(13),$error_comments).'",
            "'.count($error_comments).'",
            NOW(),
            NOW()        
        )';
    mysqli_query($connect, $query);

    return mysqli_insert_id($connect);

}