<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Slug Settings
    |--------------------------------------------------------------------------
    | Default behavior for generating slugs. Models can override by setting
    | protected $slug_column = 'custom_slug_column';
    */
    'slug' => [
        'separator'       => '-',              // Separator for slug
        'max_length'      => 150,              // Max length of slug
        'source_columns'  => ['title', 'name'], // Columns used to generate slug
        'regen_on_update' => true,             // Regenerate slug when model updates
        'column'          => 'slug',           // Default slug column (overridable in model)
    ],

    /*
    |--------------------------------------------------------------------------
    | UID Settings
    |--------------------------------------------------------------------------
    | Default UID generation config. Models can override by setting
    | protected $uid_column = 'custom_uid_column';
    */
    'uid' => [
        'prefix' => 'UID',     // Optional prefix for UID
        'length' => 16,        // Length for drivers that use random string
        'driver' => 'uniqid',  // uniqid | sha1 | uuid4 | nanoid
        'column' => 'uid',     // Default UID column (overridable in model)
    ],

    /*
    |--------------------------------------------------------------------------
    | Sequence Settings
    |--------------------------------------------------------------------------
    | Default sequence config. Models can override by setting
    | protected $sequence_column = 'custom_sequence_column';
    */
    'sequence' => [
        'prefix'  => 'ORD',            // Default prefix (ex: ORD-00001)
        'padding' => 5,                // Number padding length
        'column'  => 'order_number',   // Default column (overridable in model)
        'scoped'  => true,             // If true, keeps prefix separation
    ],

];
