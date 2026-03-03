<?php

namespace Lalit\Geocode\Tests;

use PHPUnit\Framework\TestCase;
use Lalit\Geocode\Services\MapsCoGeocoder;

class MapsCoGeocoderTest extends TestCase
{
  protected MapsCoGeocoder $geocoder;

  protected function setUp(): void
  {
    parent::setUp();
    $this->geocoder = new MapsCoGeocoder();
  }

  public function test_can_search_locations()
  {
    $result = $this->geocoder->search('California');

    $this->assertTrue($result['success']);
    $this->assertIsArray($result['data']);
    $this->assertGreaterThan(0, $result['count']);
  }

  public function test_can_get_cities_by_state()
  {
    $result = $this->geocoder->getCitiesByState('Texas');

    $this->assertTrue($result['success']);
    $this->assertIsArray($result['cities']);
  }

  public function test_search_returns_consistent_format()
  {
    $result = $this->geocoder->search('New York');

    if ($result['success'] && count($result['data']) > 0) {
      $location = $result['data'][0];
      $this->assertArrayHasKey('latitude', $location);
      $this->assertArrayHasKey('longitude', $location);
      $this->assertArrayHasKey('city', $location);
      $this->assertArrayHasKey('country', $location);
    }
  }
}
