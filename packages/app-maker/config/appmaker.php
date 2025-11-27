<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AppMaker Resources
    |--------------------------------------------------------------------------
    |
    | Register your resources here. Each resource maps a URI to a Resource class.
    | Example: 'posts' => \App\AppMaker\Resources\PostResource::class
    |
    */
    'resources' => [
        // Register your resources here
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware that will be applied to all AppMaker routes.
    |
    */
    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the route prefix and domain for AppMaker routes.
    |
    */
    'prefix' => '',
    'domain' => null,

    /*
    |--------------------------------------------------------------------------
    | Layout Configuration
    |--------------------------------------------------------------------------
    |
    | The default layout component used for AppMaker pages.
    |
    */
    'layout' => 'AuthenticatedLayout',

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Default pagination settings for tables.
    |
    */
    'default_pagination' => 25,
    'pagination_options' => [10, 25, 50, 100],

    /*
    |--------------------------------------------------------------------------
    | Date & Time Formatting
    |--------------------------------------------------------------------------
    |
    | Default date and datetime format for display.
    |
    */
    'date_format' => 'Y-m-d',
    'datetime_format' => 'Y-m-d H:i:s',

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    |
    | Enable or disable automatic authorization checks.
    |
    */
    'authorization_enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Permission Naming Convention
    |--------------------------------------------------------------------------
    |
    | The pattern used to generate permission names.
    | Available placeholders: {action}, {resource}
    |
    */
    'permission_pattern' => '{action}-{resource}',

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Enable caching of resource configurations for better performance.
    |
    */
    'cache_enabled' => true,
    'cache_ttl' => 3600, // 1 hour
];
