<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [
        'r2' => [
            'driver' => 's3',
            'key' => env('R2_ACCESS_KEY'),
            'secret' => env('R2_SECRET_KEY'),
            'region' => 'auto', // R2 不需要明確設定區域
            'bucket' => env('R2_BUCKET'),
            'endpoint' => env('R2_ENDPOINT'),
        ],
        '64r2' => [
            'driver' => 's3',
            'key' => env('R2_ACCESS_KEY'),
            'secret' => env('R2_SECRET_KEY'),
            'region' => 'auto', // R2 不需要明確設定區域
            'bucket' => env('64R2_BUCKET', '64linm'),
            'endpoint' => env('R2_ENDPOINT'),
        ],
        'mpror2' => [
            'driver' => 's3',
            'key' => env('R2_ACCESS_KEY'),
            'secret' => env('R2_SECRET_KEY'),
            'region' => 'auto', // R2 不需要明確設定區域
            'bucket' => env('MPROR2_BUCKET', 'mpro'),
            'endpoint' => env('R2_ENDPOINT'),
            'account_id' => env('R2_ACCOUNT_ID', '69036bf2096bfe2e72462cb59ff4366c'),
        ],
        'movepro' => [
            'driver' => 's3',
            'key' => env('R2_ACCESS_KEY'),
            'secret' => env('R2_SECRET_KEY'),
            'region' => 'auto', // R2 不需要明確設定區域
            'bucket' => env('MPROR2_BUCKET', 'movepro'),
            'endpoint' => env('R2_ENDPOINT'),
            'account_id' => env('R2_ACCOUNT_ID', '69036bf2096bfe2e72462cb59ff4366c'),
        ],
        //very6 使用中
        'mpro' => [
            'driver' => 'local',
            'root' => storage_path('app/mpro'),
            'url' => env('APP_URL').'/storage/mpro',
            'visibility' => 'public',
        ],

        //very6 使用中
        'apks' => [
            'driver' => 'local',
            'root' => storage_path('app/apks'),
            'url' => env('APP_URL').'/storage/apks',
            'visibility' => 'public',
        ],

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
