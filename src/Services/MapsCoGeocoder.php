<?php

namespace Lalit\Geocode\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Lalit\Geocode\Contracts\GeocoderContract;

class MapsCoGeocoder implements GeocoderContract
{
  protected string $apiUrl;
  protected ?string $apiKey;
  protected int $timeout;
  protected bool $cacheEnabled;
  protected int $cacheTtl;
  protected bool $loggingEnabled;

  public function __construct()
  {
    $this->apiUrl = 'https://api.locationiq.com/v1/autocomplete';
    $this->apiKey = null;
    $this->timeout = 15;
    $this->cacheEnabled = false;
    $this->cacheTtl = 3600;
    $this->loggingEnabled = true;

    // Try to load from config if Laravel is loaded
    if (function_exists('config')) {
      $this->apiUrl = config('geocode.maps_co.api_url', $this->apiUrl);
      $this->apiKey = config('geocode.maps_co.api_key', $this->apiKey);
      $this->timeout = config('geocode.maps_co.timeout', $this->timeout);
      $this->cacheEnabled = config('geocode.cache.enabled', $this->cacheEnabled);
      $this->cacheTtl = config('geocode.cache.ttl', $this->cacheTtl);
      $this->loggingEnabled = config('geocode.logging.enabled', $this->loggingEnabled);
    }
  }

  /**
   * Search for locations by query string
   */
  public function search(string $query, array $options = []): array
  {
    try {
      if ($this->cacheEnabled && function_exists('cache')) {
        $cacheKey = 'geocode_search_' . md5($query);
        $cached = Cache::get($cacheKey);
        if ($cached) {
          return $cached;
        }
      }

      $this->log("Searching for location: {$query}");

      $limit = $options['limit'] ?? 50;

      if ($this->apiKey) {
        $queryParams['key'] = $this->apiKey;
      }

      $queryParams = [
        'q' => $query,
        'limit' => $limit,
        'dedupe' => 1
      ];



      $response = Http::timeout($this->timeout)->get("{$this->apiUrl}", $queryParams);

      if ($response->successful()) {
        $results = $this->formatSearchResults($response->json());

        if ($this->cacheEnabled && function_exists('cache')) {
          Cache::put(
            'geocode_search_' . md5($query),
            ['success' => true, 'count' => count($results), 'data' => $results],
            $this->cacheTtl
          );
        }

        return [
          'success' => true,
          'count' => count($results),
          'data' => $results
        ];
      }

      $this->log("Search failed with status: {$response->status()}", 'warning');

      return [
        'success' => false,
        'error' => 'Failed to fetch location data',
        'status' => $response->status()
      ];
    } catch (\Exception $e) {
      $this->log("Search error: {$e->getMessage()}", 'error');

      return [
        'success' => false,
        'error' => $e->getMessage()
      ];
    }
  }

  /**
   * Get all cities in a state
   */
  public function getCitiesByState(string $state, array $options = []): array
  {
    try {
      $this->log("Getting cities for state: {$state}");

      $limit = $options['limit'] ?? 50;

      $queryParams = [
        'q' => "{$state} city",
        'limit' => $limit,
        'format' => 'json',
        'type' => 'city'
      ];

      if ($this->apiKey) {
        $queryParams['api_key'] = $this->apiKey;
      }

      $response = Http::timeout($this->timeout)->get("{$this->apiUrl}/search", $queryParams);

      if ($response->successful()) {
        $data = $response->json();
        $cities = [];

        foreach ($data as $location) {
          $address = $location['address'] ?? [];
          $locationState = $address['state'] ?? null;

          if ($locationState && stripos($locationState, $state) !== false) {
            $cities[] = [
              'name' => $location['name'] ?? null,
              'city' => $location['name'] ?? null,
              'state' => $locationState,
              'country' => $address['country'] ?? null,
              'postcode' => $address['postcode'] ?? null,
              'latitude' => (float) ($location['lat'] ?? null),
              'longitude' => (float) ($location['lon'] ?? null),
              'display_name' => $location['display_name'] ?? null,
              'type' => $location['type'] ?? 'city',
            ];
          }
        }

        return [
          'success' => true,
          'state' => $state,
          'count' => count($cities),
          'cities' => $cities
        ];
      }

      return [
        'success' => false,
        'error' => 'Failed to fetch cities data',
        'status' => $response->status()
      ];
    } catch (\Exception $e) {
      $this->log("Get cities error: {$e->getMessage()}", 'error');

      return [
        'success' => false,
        'error' => $e->getMessage()
      ];
    }
  }

  /**
   * Reverse geocode coordinates to address
   */
  public function reverseGeocode(float $latitude, float $longitude, array $options = []): array
  {
    try {
      $this->log("Reverse geocoding: LAT={$latitude}, LON={$longitude}");

      $queryParams = [
        'lat' => $latitude,
        'lon' => $longitude,
        'format' => 'json'
      ];

      if ($this->apiKey) {
        $queryParams['api_key'] = $this->apiKey;
      }

      $response = Http::timeout($this->timeout)->get("{$this->apiUrl}/reverse", $queryParams);

      if ($response->successful()) {
        $location = $response->json();
        $address = $location['address'] ?? [];

        return [
          'success' => true,
          'latitude' => $latitude,
          'longitude' => $longitude,
          'name' => $location['name'] ?? null,
          'display_name' => $location['display_name'] ?? null,
          'city' => $address['city'] ?? $address['town'] ?? null,
          'state' => $address['state'] ?? null,
          'country' => $address['country'] ?? null,
          'postcode' => $address['postcode'] ?? null,
          'address' => $address,
        ];
      }

      return [
        'success' => false,
        'error' => 'Failed to reverse geocode location',
        'status' => $response->status()
      ];
    } catch (\Exception $e) {
      $this->log("Reverse geocode error: {$e->getMessage()}", 'error');

      return [
        'success' => false,
        'error' => $e->getMessage()
      ];
    }
  }

  /**
   * Get detailed location information
   */
  public function getDetails(string $query): array
  {
    try {
      $this->log("Getting details for: {$query}");

      $queryParams = [
        'q' => $query,
        'format' => 'json',
        'limit' => 1,
        'addressdetails' => 1
      ];

      if ($this->apiKey) {
        $queryParams['api_key'] = $this->apiKey;
      }

      $response = Http::timeout($this->timeout)->get("{$this->apiUrl}/search", $queryParams);

      if ($response->successful()) {
        $data = $response->json();

        if (count($data) > 0) {
          $location = $data[0];
          $address = $location['address'] ?? [];

          return [
            'success' => true,
            'name' => $location['name'] ?? null,
            'full_address' => $location['display_name'] ?? null,
            'address_components' => $address,
            'latitude' => (float) ($location['lat'] ?? null),
            'longitude' => (float) ($location['lon'] ?? null),
            'bounding_box' => $location['boundingbox'] ?? null,
            'type' => $location['type'] ?? null,
            'class' => $location['class'] ?? null,
            'importance' => $location['importance'] ?? null,
          ];
        }

        return [
          'success' => false,
          'error' => 'Location not found'
        ];
      }

      return [
        'success' => false,
        'error' => 'Failed to fetch location details',
        'status' => $response->status()
      ];
    } catch (\Exception $e) {
      $this->log("Get details error: {$e->getMessage()}", 'error');

      return [
        'success' => false,
        'error' => $e->getMessage()
      ];
    }
  }

  /**
   * Format search results
   */
  protected function formatSearchResults(array $data): array
  {
    $results = [];

    foreach ($data as $location) {
      $address = $location['address'] ?? [];

      $results[] = [
        'id' => $address['name'] . '_' . $address['state'] . '_' . $address['country'] . '_' . $location['lat'] . '_' . $location['lon'] ?? null,
        'text' =>  $address['name'] . ', ' . $address['state'] . ', ' . $address['country'] ?? null,
        'city' => $address['name'] ?? null,
        'state' => $address['state'] ?? null,
        'country' => $address['country'] ?? null,
        'latitude' => (float) ($location['lat'] ?? null),
        'longitude' => (float) ($location['lon'] ?? null),
      ];
    }

    return $results;
  }

  /**
   * Log messages
   */
  protected function log(string $message, string $level = 'info'): void
  {
    if ($this->loggingEnabled && function_exists('logger')) {
      Log::channel(config('geocode.logging.channel', 'single'))->{$level}("[Geocode] {$message}");
    }
  }
}
