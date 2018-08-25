<?php
namespace Vanilla;

class JsonDecoder
{
  // Decode a JSON string to an array and handle possible errors
  public static function decode(string $json, int $depth = 512, int $options = 0)
  {
    $array = json_decode($json,true,$depth,$options);

    if (json_last_error() !== JSON_ERROR_NONE)
      throw new \RuntimeException("The string '{$json}' could not be decoded from JSON: " . json_last_error_msg());

    return $array;
  }
}
