<?php

return [

    'paths' => [
        realpath(__DIR__ . '/../../frontend/views'),
    ],

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views'))
    ),

];
