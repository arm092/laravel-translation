<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Package driver
    |--------------------------------------------------------------------------
    |
    | The package supports different drivers for translation management.
    |
    | Supported: "file", "database"
    |
    */
    'driver' => 'file',

    /*
    |--------------------------------------------------------------------------
    | Source locale
    |--------------------------------------------------------------------------
    |
    | The manager displays this locale beside the locale being edited. A null
    | value uses config('app.locale') while remaining independent of runtime
    | locale changes made by request middleware.
    |
    */
    'source_locale' => null,

    /*
    |--------------------------------------------------------------------------
    | Route group configuration
    |--------------------------------------------------------------------------
    |
    | The package ships with routes to handle language management. Update the
    | configuration here to configure the routes with your preferred group options.
    |
    */
    'route_group_config' => [
        'middleware' => ['web'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Authorization gate
    |--------------------------------------------------------------------------
    |
    | Optionally require a Laravel gate before the translation manager routes
    | may be accessed. Leave this value null to preserve the package's legacy
    | behavior. Production applications should combine a gate with the auth
    | middleware in route_group_config above.
    |
    */
    'authorization_gate' => null,

    /*
    |--------------------------------------------------------------------------
    | Translation methods
    |--------------------------------------------------------------------------
    |
    | Update this array to tell the package which methods it should look for
    | when finding missing translations.
    |
    */
    'translation_methods' => ['trans', '__'],

    /*
    |--------------------------------------------------------------------------
    | Scan paths
    |--------------------------------------------------------------------------
    |
    | Update this array to tell the package which directories to scan when
    | looking for missing translations.
    |
    */
    'scan_paths' => [app_path(), resource_path()],

    /* Paths (relative or absolute) and keys excluded from quality scans. */
    'scan_excluded_paths' => [storage_path(), base_path('vendor')],
    'scan_ignored_keys' => [],
    'scan_cache_path' => storage_path('framework/cache/translation-scan.json'),

    /* Locales which remain readable but cannot be changed by the manager. */
    'protected_locales' => [],

    /* Translation manager page size. Supported values: 25, 50 and 100. */
    'pagination' => 50,

    /*
    |--------------------------------------------------------------------------
    | UI URL
    |--------------------------------------------------------------------------
    |
    | Define the URL used to access the language management too.
    |
    */
    'ui_url' => 'languages',

    /*
    |--------------------------------------------------------------------------
    | Database settings
    |--------------------------------------------------------------------------
    |
    | Define the settings for the database driver here.
    |
    */
    'database' => [

        'connection' => '',

        'languages_table' => 'languages',

        'translations_table' => 'translations',
    ],
];
