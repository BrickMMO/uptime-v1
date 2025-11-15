<?php

function media_tags($identifier)
{

    global $connect;

    $query = 'SELECT tags.*
        FROM tags
        INNER JOIN media_tag
        ON media_tag.tag_id = tags.id
        WHERE media_tag.medium_id = "'.$identifier.'"';
    $result = mysqli_query($connect, $query);

    $tags = array();

    while($tag = mysqli_fetch_assoc($result))
    {
        $tags[] = $tag;
    }

    return $tags;

}

function media_fetch($identifier)
{

    if(!$identifier) return false;

    global $connect;

    $query = 'SELECT *
        FROM media
        WHERE id = "'.addslashes($identifier).'"
        LIMIT 1';
    $result = mysqli_query($connect, $query);
    
    if(mysqli_num_rows($result))
    {

        $media = mysqli_fetch_assoc($result);

        $query = 'SELECT tags.*
            FROM tags
            INNER JOIN media_tag
            ON media_tag.tag_id = tags.id
            WHERE media_tag.medium_id = "'.$identifier.'"';
        $result = mysqli_query($connect, $query);

        $tags = array();

        while($tag = mysqli_fetch_assoc($result))
        {
            $tags[] = $tag['id'];
        }

        $media['tags'] = $tags;

        return $media;

    }
    else return false;

}