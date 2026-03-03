<?php

namespace Lalit\Geocode;

use Lalit\Geocode\Services\MapsCoGeocoder;

class GeocodeService
{
  protected MapsCoGeocoder $geocoder;

  public function __construct(MapsCoGeocoder $geocoder)
  {
    $this->geocoder = $geocoder;
  }

  /**
   * Search for locations
   */
  public function search(string $query, array $options = []): array
  {
    return $this->geocoder->search($query, $options);
  }

  /**
   * Get cities by state
   */
  public function getCitiesByState(string $state, array $options = []): array
  {
    return $this->geocoder->getCitiesByState($state, $options);
  }

  /**
   * Reverse geocode
   */
  public function reverseGeocode(float $latitude, float $longitude, array $options = []): array
  {
    return $this->geocoder->reverseGeocode($latitude, $longitude, $options);
  }

  /**
   * Get location details
   */
  public function getDetails(string $query): array
  {
    return $this->geocoder->getDetails($query);
  }
}
