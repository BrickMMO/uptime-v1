<?php

function form_select_table($name, $table, $key, $value, $data = array())
{

    global $connect;

    $query = 'SELECT '.$key.','.$value.'
        FROM '.$table.'
        ORDER BY '.$value;
    $result = mysqli_query($connect, $query);

    $html = '<select name="'.$name;
    if(isset($data['multiple'])) $html .= '[]';
    $html .= '" id="'.$name.'" class="w3-input w3-border'.(isset($data['first']) ? '' : ' w3-margin-top').'"';
    if(isset($data['multiple'])) $html .= ' multiple size="5"';
    $html .= '>';

    if(isset($data['empty_key']) || isset($data['empty_value']) )
    {
        $html .= '<option value="'.(isset($data['empty_key']) ? $data['empty_key'] : '').'">
                '.(isset($data['empty_value']) ? $data['empty_value'] : '').'
            </option>';
    }

    while($record = mysqli_fetch_assoc($result))
    {
        $html .= '<option value="'.$record[$key].'"';
        if(isset($data['multiple']))
        {
            if(isset($data['selected']) && in_array($record[$key], $data['selected'])) $html .= ' selected';
        }
        else
        {
            if(isset($data['selected']) && $data['selected'] == $record[$key]) $html .= ' selected';
        }

        $html .= '>'.$record[$value].'</option>';
    }

    $html .= '</select>';

    return $html;

}

function form_select_array($name, $options, $data = array())
{

    $html = '<select name="'.$name.'" id="'.$name.'" class="w3-input w3-border'.(isset($data['first']) ? '' : ' w3-margin-top').'">';

    if(isset($data['empty_key']) || isset($data['empty_value']) )
    {
        $html .= '<option value="'.(isset($data['empty_key']) ? $data['empty_key'] : '').'">
                '.(isset($data['empty_value']) ? $data['empty_value'] : '').'
            </option>';
    }

    foreach($options as $value => $option)
    {
        $html .= '<option value="'.$value.'"';
        if(isset($data['selected']) && $data['selected'] == $value) $html .= ' selected';
        $html .= '>'.$option.'</option>';
    }

    $html .= '</select>';

    return $html;

}