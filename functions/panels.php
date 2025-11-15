<?php

// Fetch panel data based on city_id
function panels_data_by_city($city_id)
{
    global $connect;

    $query = 'SELECT * 
        FROM panels 
        WHERE city_id = "'.mysqli_real_escape_string($connect, $city_id).'"';
    $result = mysqli_query($connect, $query);

    $panel_data = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $panel_data[] = $row;
    }

    return $panel_data;
}

// Update panel value based on panel ID
function panels_update_value($panel_id, $new_value)
{
    global $connect;

    $query = 'UPDATE panels SET 
        value = "'.mysqli_real_escape_string($connect, $new_value).'" 
        WHERE id = "'.mysqli_real_escape_string($connect, $panel_id).'"';
    $result = mysqli_query($connect, $query);
    
    return $result;
}

// Group panel data by cartridge and port
function panels_group_data_by_cartridge($panel_data)
{
    $power_lever = [];
    $current_cartridge = [];
    $cartridge_data = [];

    foreach ($panel_data as $panel) {
        if ($panel['port'] == 'a') {
            $power_lever = ['id' => $panel['id'], 'value' => $panel['value']];
        }
        if ($panel['port'] == '1') {
            $current_cartridge = ['id' => $panel['id'], 'value' => $panel['value']];
        }
        if ($panel['cartridge'] != null) {
            $cartridge = $panel['cartridge'];
            $port = $panel['port'];
            $value = $panel['value'];

            if (!isset($cartridge_data[$cartridge])) {
                $cartridge_data[$cartridge] = [];
            }

            if (!isset($cartridge_data[$cartridge][$port])) {
                $cartridge_data[$cartridge][$port] = [];
            }

            $cartridge_data[$cartridge][$port][] = ['id' => $panel['id'], 'value' => $value];
        }
    }

    return [$power_lever, $current_cartridge, $cartridge_data];
}

// Fetch panel data based on cartridge and city_id
function panels_data_by_cartridge($cartridge, $city_id, $connect)
{
    $query = "SELECT * FROM panels WHERE city_id = " . mysqli_real_escape_string($connect, $city_id) . " 
              AND cartridge = '" . mysqli_real_escape_string($connect, $cartridge) . "'";
    $result = mysqli_query($connect, $query);

    $cartridge_data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $port = $row['port'];
        if (!isset($cartridge_data[$port])) {
            $cartridge_data[$port] = [];
        }
        $cartridge_data[$port][] = ['id' => $row['id'], 'value' => $row['value']];
    }

    return $cartridge_data;
}

function panels_new($city_id)
{
    global $connect;

    $query = 'SELECT *
        FROM panels
        WHERE city_id = "'.$city_id.'"';
    $result = mysqli_query($connect, $query);

    if(mysqli_num_rows($result))
    {
        return;
    }

    $panels = [
        [
            'port' => 'a',
            'cartridge' => null,
            'value' => 'OFF',
        ],
        [
            'port' => '1',
            'cartridge' => null,
            'value' => 'blue',
        ],
        ['port' => 'b', 'cartridge' => 'blue',  'value' => '0'],
        ['port' => 'c', 'cartridge' => 'blue',  'value' => '0'],
        ['port' => 'd', 'cartridge' => 'blue',  'value' => '0'],
        ['port' => 'b', 'cartridge' => 'red',  'value' => '0'],
        ['port' => 'c', 'cartridge' => 'red',  'value' => '0'],
        ['port' => 'd', 'cartridge' => 'red',  'value' => '0'],
        ['port' => 'b', 'cartridge' => 'brown',  'value' => '0'],
        ['port' => 'c', 'cartridge' => 'brown',  'value' => '0'],
        ['port' => 'd', 'cartridge' => 'brown',  'value' => '0'],
        ['port' => 'b', 'cartridge' => 'yellow',  'value' => '0'],
        ['port' => 'c', 'cartridge' => 'yellow',  'value' => '0'],
        ['port' => 'd', 'cartridge' => 'yellow',  'value' => '0'],
        ['port' => '2', 'cartridge' => 'blue',  'value' => 'OFF'],
        ['port' => '3', 'cartridge' => 'blue',  'value' => 'OFF'],
        ['port' => '4', 'cartridge' => 'blue',  'value' => 'OFF'],
        ['port' => '2', 'cartridge' => 'red',  'value' => 'OFF'],
        ['port' => '3', 'cartridge' => 'red',  'value' => 'OFF'],
        ['port' => '4', 'cartridge' => 'red',  'value' => 'OFF'],
        ['port' => '2', 'cartridge' => 'brown',  'value' => 'OFF'],
        ['port' => '3', 'cartridge' => 'brown',  'value' => 'OFF'],
        ['port' => '4', 'cartridge' => 'brown',  'value' => 'OFF'],
        ['port' => '2', 'cartridge' => 'yellow',  'value' => 'OFF'],
        ['port' => '3', 'cartridge' => 'yellow',  'value' => 'OFF'],
        ['port' => '4', 'cartridge' => 'yellow',  'value' => 'OFF'],
    ];

    foreach($panels as $panel)
    {
        $query = 'INSERT INTO panels (
                `port`,
                `cartridge`,
                `value`,
                `city_id`,
                `created_at`,
                `updated_at`
            ) VALUES (
                "'.$panel['port'].'",
                '.($panel['cartridge'] ? '"'.$panel['cartridge'].'"' : 'NULL').',
                "'.$panel['value'].'",
                "'.$city_id.'",
                NOW(),
                NOW()
            )';
        mysqli_query($connect, $query);
    }
}