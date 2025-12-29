<?php

/*
    |--------------------------------------------------------------------------
    | Queue Types
    |--------------------------------------------------------------------------
    |

    */

return [

    'vanilla' => [
        'name'             => 'Vanilla',
        'item_consume'     => false,
        'character_submit' => false,
        'image_upload'     => false,
    ],

    'guilds' => [
        'name'             => 'Guilds',
        'item_consume'     => false,
        'character_submit' => false,
        'image_upload'     => true,
    ],
];
