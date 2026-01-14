<?php

return [
    'enabled' => env('DEBUGBAR', 0) == 1,
    'secret' => env('DEBUGBAR_SECRET', null),

    'collectors' => [
        'phpinfo'         => true,
        'messages'        => true,
        'time'            => true,
        'memory'          => true,
        'exceptions'      => true,
        'log'             => true,
        'db'              => true,
        'views'           => true,
        'route'           => true,
        'auth'            => true,
        'gate'            => true,
        'session'         => true,
        'symfony_request' => true,
        'mail'            => true,
        'laravel'         => true,
        'events'          => false,
        'default_request' => false,
        'logs'            => false,
        'files'           => false,
        'config'          => false,
        'cache'           => true,
        'models'          => true,
        'livewire'        => false,
        'jobs'            => false,
    ],

    'options' => [
        'auth' => [
            'show_name' => true,
        ],
        'db' => [
            'with_params'       => true,
            'backtrace'         => true,
            'backtrace_exclude_paths' => [],
            'timeline'          => false,
            'duration_background'  => true,
            'explain' => [
                'enabled' => false,
                'types' => ['SELECT'],
            ],
            'hints'             => false,
            'show_copy'         => true,
            'slow_threshold'    => false,
        ],
        'mail' => [
            'full_log' => false,
        ],
        'views' => [
            'timeline' => false,
            'data'     => false,
        ],
        'route' => [
            'label' => true,
        ],
        'logs' => [
            'file' => null,
        ],
        'cache' => [
            'values' => true,
        ],
    ],

    /*
     | Inject Debugbar in Response
     | This injects the debugbar in HTML responses.
     */
    'inject' => true,

    /*
     | DebugBar route prefix
     | This is the prefix for the debugbar routes.
     */
    'route_prefix' => '_debugbar',

    /*
     | DebugBar route domain
     | Set to a specific domain for the debugbar routes.
     */
    'route_domain' => null,

    /*
     | DebugBar theme
     | Use 'auto' to follow system preference, or 'light' or 'dark'.
     */
    'theme' => 'auto',

    /*
     | Clockwork Integration
     */
    'clockwork' => false,

    /*
     | Editor Configuration
     */
    'editor' => env('DEBUGBAR_EDITOR', 'phpstorm'),

    /*
     | Remote sites domain
     */
    'remote_sites_path' => env('DEBUGBAR_REMOTE_SITES_PATH', ''),
    'local_sites_path' => env('DEBUGBAR_LOCAL_SITES_PATH', ''),

    /*
     | Vendors
     */
    'include_vendors' => true,

    /*
     | Capture Ajax Requests
     */
    'capture_ajax' => true,
    'add_ajax_timing' => false,
    'ajax_handler_auto_show' => true,
    'ajax_handler_enable_tab' => true,

    /*
     | Open Handler URL
     */
    'open_handler_url' => null,

    /*
     | Error Handler
     */
    'error_handler' => false,
];
