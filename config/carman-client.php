<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Carman API
    |--------------------------------------------------------------------------
    |
    | The base url of the Carman API (including the version), a personal access
    | token created in Carman under "API tokens" and the slug of the company
    | whose data should be synced to this website.
    |
    */

    'api_url' => env('CARMAN_API_URL'),

    'api_token' => env('CARMAN_API_TOKEN'),

    'company' => env('CARMAN_COMPANY'),

    /*
    |--------------------------------------------------------------------------
    | Vehicle permalinks
    |--------------------------------------------------------------------------
    |
    | Vehicle detail pages are available on /{car_slug}/{vehicle}/. Flush the
    | rewrite rules after changing this value.
    |
    */

    'car_slug' => 'wagenpark',

];
