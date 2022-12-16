<?php

return [
    'web_proxy'   => env('LOAD_BALANCER_WEB_PROXIES'),
    'https_proxy' => env('LOAD_BALANCER_HTTPS_PROXIES'),
    'interface'   => env('LOAD_BALANCER_INTERFACES'),
];
