<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Path to the Vite Manifest
    |--------------------------------------------------------------------------
    |
    | This tells Laravel where the Vite build manifest is located in production.
    | It must point to the same folder that contains manifest.json.
    |
    */
    'manifest_path' => base_path('public_html/build/.vite/manifest.json'),

    /*
    |--------------------------------------------------------------------------
    | Dev Server URL
    |--------------------------------------------------------------------------
    |
    | Leave this null to force Laravel to always use the production manifest.
    |
    */
    'dev_server_url' => null,
];
