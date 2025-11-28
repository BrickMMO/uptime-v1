<?php

function navigation_array($selected = false)
{

    $navigation = [
        [
            'title' => 'Uptime',
            'sections' => [
                [
                    'title' => 'Uptime',
                    'id' => 'admin-content',
                    'pages' => [
                        [
                            'icon' => 'uptime',
                            'url' => '/admin/dashboard',
                            'title' => 'Uptime',
                            'sub-pages' => [
                                [
                                    'title' => 'Dashboard',
                                    'url' => '/admin/dashboard',
                                    'colour' => 'red',
                                ],[
                                    'title' => 'Add Asset',
                                    'url' => '/admin/add',
                                    'colour' => 'red',
                                ],[
                                    'title' => 'Recent Issues',
                                    'url' => '/admin/issues',
                                    'colour' => 'red',
                                ],[
                                    'br' => '---',
                                ],[
                                    'title' => 'Visit Uptime App',
                                    'url' => 'https://uptime.brickmmo.com',
                                    'colour' => 'orange',
                                    'icon' => 'fa-solid fa-arrow-up-right-from-square',
                                ],[
                                    'br' => '---',
                                ],[
                                    'title' => 'Uptime Report',
                                    'url' => '/uptime/events',
                                    'colour' => 'orange',
                                    'icons' => 'bm-uptime',
                                ],[
                                    'title' => 'Stats Report',
                                    'url' => '/stas/events',
                                    'colour' => 'orange',
                                    'icons' => 'bm-stats',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    if($selected)
    {
        
        $selected = '/'.$selected;
        $selected = str_replace('//', '/', $selected);
        $selected = str_replace('.php', '', $selected);
        $selected = str_replace('.', '/', $selected);
        $selected = substr($selected, 0, strpos($selected, '/'));

        foreach($navigation as $levels)
        {

            foreach($levels['sections'] as $section)
            {

                foreach($section['pages'] as $page)
                {

                    if(strpos($page['url'], $selected) === 0)
                    {
                        return $page;
                    }

                }

            }

        }

    }

    return $navigation;

}