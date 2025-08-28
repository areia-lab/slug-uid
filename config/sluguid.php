<?php

return [
    'slug' => [
        'separator' => '-',
        'max_length' => 150,
        'source_columns' => ['title', 'name'],
        'regen_on_update' => true,
    ],
    'uid' => [
        'prefix' => 'UID',
        'length' => 16,
        'driver' => 'uniqid', // uniqid, sha1, uuid4, nanoid
    ],
    'sequence' => [
        'prefix' => 'ORD',
        'padding' => 5,
        'column' => 'post_sequence',
        'scoped' => true,
    ],
];
