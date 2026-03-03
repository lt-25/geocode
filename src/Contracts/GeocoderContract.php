<?php

namespace Lalit\Geocode\Contracts;

interface GeocoderContract
{
  /**
   * Search for locations by query
   */
  public function search(string $query, array $options = []): array;

  /**
   * Get cities by state
   */
  public function getCitiesByState(string $state, array $options = []): array;

  /**
   * Reverse geocode coordinates to address
   */
  public function reverseGeocode(float $latitude, float $longitude, array $options = []): array;

  /**
   * Get detailed location information
   */
  public function getDetails(string $query): array;
}
