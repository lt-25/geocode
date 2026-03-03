<?php

namespace Lalit\Geocode\Providers;

use Illuminate\Support\ServiceProvider;
use Lalit\Geocode\Services\MapsCoGeocoder;

class GeocodeServiceProvider extends ServiceProvider
{
  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    // Publish configuration file
    $this->publishes([
      __DIR__ . '/../../config/geocode.php' => config_path('geocode.php'),
    ], 'geocode-config');

    // Merge default config
    $this->mergeConfigFrom(
      __DIR__ . '/../../config/geocode.php',
      'geocode'
    );
  }

  /**
   * Register any application services.
   */
  public function register(): void
  {
    $this->app->singleton('geocode', function ($app) {
      return new MapsCoGeocoder();
    });
  }

  /**
   * Get the services provided by the provider.
   */
  public function provides(): array
  {
    return ['geocode'];
  }
}
