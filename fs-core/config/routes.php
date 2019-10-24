<?php
use Cake\Routing\Router;

Router::plugin(
    'FsCore',
    ['path' => '/fs_core'],
    function ($routes) {
        $routes->fallbacks('DashedRoute');
    }
);
