<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | Here you can indicate which driver you want to use.
    |
    | Available Drivers: "phpspreadsheet", "spout"
    |
    */
    'driver' => 'phpspreadsheet',

    'reader' => [

        /*
        |--------------------------------------------------------------------------
        | Default Loader
        |--------------------------------------------------------------------------
        |
        | Here you can indicate which should be the default disk that Excel should
        | load your files from. You can choose any disk from filesystems.php.
        |
        | Available Drivers: "filesystem", "native"
        |
        */
        'loader' => [
            'driver'      => 'filesystem',
            'defaultDisk' => 'local',
        ],
    ],

    'writer' => [

        /*
         |--------------------------------------------------------------------------
         | Default Loader
         |--------------------------------------------------------------------------
         |
         | Here you can indicate which should be the default disk that Excel should
         | writer your files to. You can choose any disk from filesystems.php.
         |
         | Available Drivers: "filesystem", "native"
         |
         */
        'loader' => [
            'driver'      => 'filesystem',
            'defaultDisk' => 'local',
        ],
    ]
];
