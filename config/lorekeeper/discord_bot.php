<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Discord Bot Settings
    |--------------------------------------------------------------------------
    |
    | This controls various settings relating to the Discord bot and its
    | functionality.
    |
    */

    'rules_channel'     => null,
    'questions_channel' => null,

    'welcome_channel_id' => null,

    // BANNED WORDS
    // refer to http://www.rsdb.org/full for examples
    // upload banned words to the filemanager in a file called banned_words.txt, one word per line

    // list of registered commands and their descriptions
    'commands' => [
        [
            'name'        => 'help',
            'description' => 'Displays a list of commands.',
        ],
        [
            'name'        => 'ping',
            'description' => 'Checks delay.',
        ],
        [
            'name'        => 'rank',
            'description' => 'Displays level, EXP, etc. information by generating a rank card.',
            'options'     => [
                [
                    'name'        => 'user',
                    'description' => 'The user whose rank is to be displayed. By default, it is the invoker.',
                    'type'        => 6,
                    'required'    => false,
                ],
            ],
        ],
        [
            'name'        => 'leaderboard',
            'description' => 'Shows a leaderboard of the top 10 users, along with the invokers position.',
        ],
        [
            'name'        => 'grant',
            'description' => 'Grants exp or levels to a user. The invoker must be on-site staff.',
            'options'     => [
                [
                    'name'        => 'user',
                    'description' => 'The user to grant exp or levels to.',
                    'type'        => 6,
                    'required'    => true,
                ],
                [
                    'name'        => 'type',
                    'description' => 'The type of grant. Can be exp or level.',
                    'type'        => 3,
                    'required'    => true,
                    'choices'     => [
                        [
                            'name'  => 'exp',
                            'value' => 'exp',
                        ],
                        [
                            'name'  => 'level',
                            'value' => 'level',
                        ],
                    ],
                ],
                [
                    'name'        => 'amount',
                    'description' => 'The amount of exp or levels to grant.',
                    'type'        => 4,
                    'required'    => true,
                ],
            ],
        ],
        [
            'name'        => 'roles',
            'description' => 'Applies roles to your Discord account based on your site information and Discord level.',
        ],
        [
            'name'        => 'roll',
            'description' => 'Rolls a dice.',
            'options'     => [
                [
                    'name'        => 'sides',
                    'description' => 'The dice to roll.',
                    'type'        => 3,
                    'required'    => true,
                ],
                [
                    'name'        => 'quantity',
                    'description' => 'The quantity of dice to roll.',
                    'type'        => 4,
                    'required'    => false,
                ],
            ],
        ],
    ],

    // Channels to ignore for EXP rewards
    // Commands will still work in them, however
    //
    'ignored_channels' => [
        // put channel IDs here, e.g.
        // 0000000000000000000
    ],

    // channel to send level up messages to (if site setting is set to 2)
    'level_up_channel' => null,

    // These settings pertain to the generation of rank cards
    'rank_cards' => [
        // Color used for the background of the rank card
        'background_color' => '#fff',
        // Color used for background elements, such as the empty part of
        // the EXP bar
        'accent_color' => '#ddd',
        // Opacity of accent backgrounds. Set to null or 100 for full opacity
        'accent_opacity' => 75,
        // Color used for the filled portion of the EXP bar
        'exp_bar' => '#62bd77',

        // Color used for regular text
        'text_color' => '#000',
        // Color used for EXP bar text. Set to null to use regular text color
        'exp_text' => null,
        // This should be a path relative to the site's public directory
        'font_file' => 'webfonts/RobotoCondensed-Regular.ttf',

        // Image file to insert as a "logo" into the corner of the rank card
        // Should be relative to the site's public directory (e.g. 'images/meta-image.png')
        // Set to null to disable
        'logo_insert' => 'images/meta-image.png',
    ],

    // these are roles for labels like FTO, Non-Owner, Owner, etc.
    'roles' => [
        'owner'     => null,
        'non_owner' => null,
        'fto'       => null,
        'adult'     => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | env Values
    |--------------------------------------------------------------------------
    |
    | Do not change these! This is used so that values in the env can be cached
    | for performance.
    |
    */

    'env' => [
        // bot token is the token for the bot user
        'token'                => env('DISCORD_BOT_TOKEN'),

        // error channel is specifically for errors that occur in the bot
        'error_channel'        => env('DISCORD_ERROR_CHANNEL'),

        // log channel is used for logs such as edits, deletions, etc.
        'log_channel'      => env('DISCORD_LOG_CHANNEL'),

        // guild ID is the id of the Discord server the bot should be active in
        'guild_id'             => env('DISCORD_GUILD_ID'),

        // WEBHOOKS
        'webhooks' => [
            'staff'        => env('DISCORD_STAFF_WEBHOOK_URL'), // staff is an all-purpose webhook for on-site submissions
            'announcement' => env('DISCORD_ANNOUNCEMENT_WEBHOOK_URL'),
        ],
    ],
];
