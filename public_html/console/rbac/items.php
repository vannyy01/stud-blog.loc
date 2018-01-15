<?php
return [
    'managePosts' => [
        'type' => 2,
        'description' => 'Manage Posts',
    ],
    'manageUsers' => [
        'type' => 2,
        'description' => 'Manage users',
    ],
    'moderator' => [
        'type' => 1,
        'description' => 'Moderator',
        'children' => [
            'managePosts',
        ],
    ],
    'admin' => [
        'type' => 1,
        'description' => 'Administrator',
        'children' => [
            'moderator',
            'manageUsers',
        ],
    ],
];
