<?php

return [
    'delete' => true,
    'repos' => [
        [
            'repo' => 'tmarois/laravel-standard',
            'paths' => [
                [
                    'path' => 'docs',
                    'output' => 'docs/atlas/standards',
                ],
            ],
            'ignore' => [],
        ],
        [
            'repo' => 'tmarois/atlas-laravel',
            'paths' => [
                [
                    'path' => 'docs/features',
                    'output' => 'docs/atlas-laravel',
                ],
            ],
            'ignore' => [],
        ],
        [
            'repo' => 'tmarois/atlas-ui',
            'paths' => [
                [
                    'path' => 'docs',
                    'output' => 'docs/atlas-ui',
                ],
            ],
            'ignore' => [],
        ],
    ],
];
