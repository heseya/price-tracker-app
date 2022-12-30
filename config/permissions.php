<?php

declare(strict_types=1);

return [
    'required' => [
        'auth.check_identity',
        'products.show',
        'products.show_details',
    ],

    'internal' => [
        [
            'name' => 'configure',
            'display_name' => 'Ability to change Price Tracker settings',
        ],
    ],
];
