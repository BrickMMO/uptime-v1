<?php

function user_avatar($id, $absolute = false)
{
    $user = user_fetch($id);
    return $user['avatar'] ? $user['avatar'] : 'https://cdn.brickmmo.com/images@1.0.0/no-avatar.png';
}

function user_name($id)
{
    $user = user_fetch($id);
    return $user['first'].' '.$user['last'];
}

function user_fetch($identifier, $field = false)
{

    if(ENV_SSO)
    {
        
        global $connect;

        if($field)
        {
            $query = 'SELECT *
                FROM users
                WHERE '.$field.' = "'.addslashes($identifier).'"
                LIMIT 1';
        }
        else
        {
            $query = 'SELECT *
                FROM users
                WHERE id = "'.addslashes($identifier).'"
                OR email = "'.addslashes($identifier).'"
                OR github_username = "'.addslashes($identifier).'"
                OR (reset_hash = "'.addslashes($identifier).'" AND reset_hash != "")
                OR (verify_hash = "'.addslashes($identifier).'" AND verify_hash != "")
                LIMIT 1';
        }

        $result = mysqli_query($connect, $query);

        if(mysqli_num_rows($result)) return mysqli_fetch_assoc($result);
        else return false;

    }
    elseif(is_numeric($identifier))
    {
        
        $url = ENV_SSO_DOMAIN.'/api/user/'.$identifier.'<br>';

        $data = fetch_json($url);

        return $data['user'];

    }

    return false;

}