<?php

/*
 * Validation Functions
 * 
 */

function validate_reserved_urls($url)
{

    $reserved_urls = array(
        'url',
        'brickmmo',
        'admin',
    );

    if(in_array($url,$reserved_urls))
    {
        return true;
    }
    else
    {
        return false;
    }
}

/*
 * Basic email validation
 */
function validate_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/*
 * Basic email validation
 */
function validate_email_exists($email, $table, $id = false)
{
    global $connect;

    $query = 'SELECT email
        FROM '.$table.'
        WHERE email = "'.addslashes($email).'" ';
    if($id) $query .= 'AND id != '.$id.' ';
    $query .= 'LIMIT 1';
    $result = mysqli_query($connect, $query);

    return mysqli_num_rows($result) ? true : false;
}

/*
 * Basic url validation
 */
function validate_url_exists($url, $table, $id = false)
{
    global $connect;

    $query = 'SELECT url
        FROM '.$table.'
        WHERE url = "'.addslashes($url).'" ';
    if($id) $query .= 'AND id != '.$id.' ';
    $query .= 'LIMIT 1';
    $result = mysqli_query($connect, $query);

    return mysqli_num_rows($result) ? true : false;
}

/*
 * Basic validation for password
 */
function validate_password($password)
{
    return !empty($password); // Just checking if password is not empty
}

/*
 * Basic validation for blanks
 */
function validate_blank($value)
{
    return !empty($value);
}

/*
 * Basic validation for a GitHub username
 */
function validate_github($github)
{
    return preg_match('/^[a-zA-Z0-9\-]{6,39}$/', $github);
}

/*
 * Basic validation for a GitHub username
 */
function validate_alpha_numeric($value)
{
    return preg_match('/^[a-zA-Z0-9]+$/', $value);
}

/*
 * Basic validation for images uploads
 */
function validate_image($value)
{
    if($value['error'] != '0') return false;
    elseif(!in_array($value['type'], array('image/jpeg', 'image/jpg', 'image/png', 'image/gif'))) return false;
    else return true;   
}
