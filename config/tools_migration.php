<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tools migration import denylist
    |--------------------------------------------------------------------------
    |
    | Common tables are importable unless listed here.
    | These tables are preserved in the new tools database during migration.
    |
    | You can also append tables with TOOLS_MIGRATION_NOT_ALLOWED_TABLES
    | in .env as a comma separated list.
    |
    */
    'not_allowed_tables' => array_values(array_unique(array_merge([
        'compats',
        'compats_newsletter',
        'compats_options',
        'compats_product',
        'failed_jobs',
        'dashboard',
        'migrations',
        'password_resets',
        'password_reset_tokens',
        'personal_access_tokens',
    ], array_filter(array_map(
        'trim',
        explode(',', env('TOOLS_MIGRATION_NOT_ALLOWED_TABLES', ''))
    ))))),
];
