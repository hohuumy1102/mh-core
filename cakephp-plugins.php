<?php
$baseDir = dirname(dirname(__FILE__));
return [
    'plugins' => [
        'Backend' => $baseDir . '/plugins/Backend/',
        'Bake' => $baseDir . '/vendor/cakephp/bake/',
        'DebugKit' => $baseDir . '/vendor/cakephp/debug_kit/',
        'Migrations' => $baseDir . '/vendor/cakephp/migrations/',
        'Sluggable' => $baseDir . '/plugins/Sluggable/'
    ]
];