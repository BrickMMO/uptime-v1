<?php

function image_type_from_base64($image_data)
{

    if(string_is_base64($image_data))
    {
        $image_data = base64_decode($image_data);
    }

    $image_file = finfo_open();
    $image_type = finfo_buffer($image_file, $image_data, FILEINFO_MIME_TYPE);

    return $image_type;

}

function image_to_bas64($url)
{

    $image_data = file_get_contents($url);

    $image_type = image_type_from_base64($image_data);

    $base64_image = 'data:'.$image_type.';base64, '.base64_encode($image_data);

    return $base64_image;

}