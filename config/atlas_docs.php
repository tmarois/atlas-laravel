<?php

return [
    'delete' => true,
    'repos' => [
        [
            'repo' => 'atlasphp/atlas-laravel',
            'paths' => [
                [
                    'path' => 'docs',
                    'output' => 'docs/atlas/atlas-laravel',
                ],
            ],
            'ignore' => ['README.md'],
        ],
        [
            'repo' => 'atlasphp/atlas-cli',
            'paths' => [
                [
                    'path' => 'docs',
                    'output' => 'docs/atlas/atlas-cli',
                ],
            ],
            'ignore' => [],
        ],
    ],
];
