<?php

return [
    'url' => env('API_URL_DSO'),

    'token' => hash('sha256', date('Ymd') . env('DSO_TOKEN', null)),
];
