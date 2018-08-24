<?php
namespace Vanilla\Tests\Objects;

class Person
{
  public $firstName;
  public $lastName;
  public $address;
  public $tags;
  public $rank;

  public function __construct(array $variables = [])
  {
    foreach ($variables as $name => $value)
      $this->$name = $value;
  }
}
