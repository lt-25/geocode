<?php

return [
  /*
    |--------------------------------------------------------------------------
    | Geocode Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the geocoding service provider and API settings
    |
    */

  'provider' => env('GEOCODE_PROVIDER', 'location-iq'),

  /*
    |--------------------------------------------------------------------------
    | Maps.co Configuration
    |--------------------------------------------------------------------------
    */
  'maps_co' => [
    'api_url' => env('GEOCODE_MAPS_CO_URL', 'https://api.locationiq.com/v1/autocomplete'),
    'api_key' => env('GEOCODE_MAPS_CO_KEY', null),
    'timeout' => env('GEOCODE_TIMEOUT', 15),
  ],

  /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    | Enable caching for geocoding results to reduce API calls
    */
  'cache' => [
    'enabled' => env('GEOCODE_CACHE_ENABLED', false),
    'ttl' => env('GEOCODE_CACHE_TTL', 3600), // 1 hour
    'store' => env('GEOCODE_CACHE_STORE', 'default'),
  ],

  /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
  'rate_limit' => [
    'enabled' => env('GEOCODE_RATE_LIMIT', false),
    'requests' => env('GEOCODE_RATE_LIMIT_REQUESTS', 60),
    'period' => env('GEOCODE_RATE_LIMIT_PERIOD', 60), // seconds
  ],

  /*
    |--------------------------------------------------------------------------
    | Default Search Limit
    |--------------------------------------------------------------------------
    | Maximum number of results returned per search
    */
  'default_limit' => env('GEOCODE_DEFAULT_LIMIT', 50),

  /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
  'logging' => [
    'enabled' => env('GEOCODE_LOGGING', true),
    'channel' => env('GEOCODE_LOG_CHANNEL', 'single'),
  ],
];
