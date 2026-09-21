<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        // `serve` is off deliberately. It registered a framework route at
        // /storage/{path} (signed-URL gated, so private files were never exposed)
        // which shadowed the `storage.file` fallback that serves real uploads and
        // sent Cache-Control: no-store. Nothing in the app reads this disk.
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => false,
            'throw' => false,
            'report' => false,
        ],

        // Public uploads. The root is env-driven so production can put them
        // OUTSIDE the git deploy directory — Hostinger's auto-deploy does a clean
        // checkout, which destroys anything the repo does not contain.
        'public' => [
            'driver' => 'local',
            'root' => env('FILESYSTEM_PUBLIC_ROOT') ?: storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
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
            'throw' => false,
            'report' => false,
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
        // Kept in step with the `public` disk root above, so `storage:link` points
        // at wherever uploads actually live. The link is only a fast path: when it
        // is missing, `/storage/...` still resolves via the `storage.file` route.
        public_path('storage') => env('FILESYSTEM_PUBLIC_ROOT') ?: storage_path('app/public'),
    ],

];
