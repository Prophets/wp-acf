<?php

return [
    'actions' => [
        [
            'name' => 'plugins_loaded',
            'use' => [
                \Prophets\WPACF\Actions\AcfJsonAction::class
            ]
        ],
    ],
    'filters' => [],
];

