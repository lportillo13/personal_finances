<?php

return [
    'manifest' => storage_path('framework/vite/manifest.json'),
    'hot_file' => storage_path('framework/vite/hot'),
    'build_path' => env('VITE_BUILD_PATH', 'build'),
    'dev_server_url' => env('VITE_DEV_SERVER_URL'),
];
