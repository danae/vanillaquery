<?php
namespace Vanilla\Tests\Objects;

class Address
{
  public $city;
  public $country;

  public function __construct(array $variables = [])
  {
    foreach ($variables as $name => $value)
      $this->$name = $value;
  }
}
