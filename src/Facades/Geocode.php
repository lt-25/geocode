<?php

namespace Lalit\Geocode\Facades;

use Illuminate\Support\Facades\Facade;

class Geocode extends Facade
{
  protected static function getFacadeAccessor()
  {
    return 'geocode';
  }
}
