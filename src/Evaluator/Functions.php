<?php
namespace Vanilla\Evaluator;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class Functions
{
  // Variables
  private $propertyAccessor;

  // Constructor
  public function __construct(PropertyAccessorInterface $propertyAccessor = null)
  {
    $this->propertyAccessor = $propertyAccessor ?? PropertyAccess::createPropertyAccessor();
  }

  // Wrap a value function in an object function wrapper
  private function wrapObject(callable $function, string $fieldName): callable
  {
    return function(object $object) use ($function, $fieldName) {
      // Check if the field is readable
      if (!$this->propertyAccessor->isReadable($object,$fieldName))
        return false;

      // Get the field from the object
      $value = $this->propertyAccessor->getValue($object,$fieldName);

      // Call the original function and return its result
      return $function($value);
    };
  }

  // Check if two values have the same type
  private function assertTypes($a, $b): bool
  {
    return gettype($a) == gettype($b);
  }

  // Return a function for a specified function name and field name
  public function fn(string $functionName, string $fieldName, ...$args): callable
  {
    $functions = [
      // Comparison functions
      '$eq' => function($expected) use ($fieldName) {
        return $this->wrapObject(function($value) use ($expected) {
          return $value === $expected;
        },$fieldName);
      },
      '$ne' => function($expected) use ($fieldName) {
        return $this->wrapObject(function($value) use ($expected) {
          return $value !== $expected;
        },$fieldName);
      },
      '$lt' => function($reference) use ($fieldName) {
        return $this->wrapObject(function($value) use ($reference) {
          return $this->assertTypes($value,$reference) && $value < $reference;
        },$fieldName);
      },
      '$lte' => function($reference) use ($fieldName) {
        return $this->wrapObject(function($value) use ($reference) {
          return $this->assertTypes($value,$reference) && $value <= $reference;
        },$fieldName);
      },
      '$gt' => function($reference) use ($fieldName) {
        return $this->wrapObject(function($value) use ($reference) {
          return $this->assertTypes($value,$reference) && $value > $reference;
        },$fieldName);
      },
      '$gte' => function($reference) use ($fieldName) {
        return $this->wrapObject(function($value) use ($reference) {
          return $this->assertTypes($value,$reference) && $value >= $reference;
        },$fieldName);
      },

      // Range comparison functions
      '$between' => function($min, $max) use ($fieldName) {
        return $this->wrapObject(function($value) use ($min, $max) {
          return $this->assertTypes($value,$min) && $this->assertTypes($value,$max) && $min <= $value && $value <= $max;
        },$fieldName);
      },
      '$beyond' => function($min, $max) use ($fieldName) {
        return $this->wrapObject(function($value) use ($min, $max) {
          return $this->assertTypes($value,$min) && $this->assertTypes($value,$max) && ($value < $min || $max < $value);
        },$fieldName);
      },

      // Multiple comparison functions
      '$in' => function(...$array) use ($fieldName) {
        return $this->wrapObject(function($value) use ($array) {
          return in_array($value,$array,true);
        },$fieldName);
      },
      '$nin' => function(...$array) use ($fieldName) {
        return $this->wrapObject(function($value) use ($array) {
          return !in_array($value,$array,true);
        },$fieldName);
      },

      // Element functions
      '$exists' => function(bool $exists) use ($fieldName) {
        return function(object $object) use ($exists, $fieldName) {
          if ($exists)
            return $this->propertyAccessor->isReadable($object,$fieldName);
          else
            return !$this->propertyAccessor->isReadable($object,$fieldName);
        };
      },
      '$type' => function(string $typeName) use ($fieldName) {
        return $this->wrapObject(function($value) use ($typeName) {
          return gettype($value) == $typeName;
        },$fieldName);
      },
      '$class' => function(string $className) use ($fieldName) {
        return $this->wrapObject(function($value) use ($className) {
          return is_object($value) && is_a($value,$className);
        },$fieldName);
      },

      // Evaluator functions
      '$regex' => function(string $pattern) use ($fieldName) {
        return $this->wrapObject(function($value) use ($pattern) {
          return preg_match($pattern,$value) === 1;
        },$fieldName);
      },

      // Array functions
      '$all' => function(...$array) use ($fieldName) {
        return $this->wrapObject(function($value) use ($array) {
          if (!is_array($value) && !is_a($value,\Traversable::class))
            return false;

          return !in_array(false,array_map(function($item) use ($value) {
            return in_array($item,$value,true);
          },$array),true);
        },$fieldName);
      },
      '$any' => function(...$array) use ($fieldName) {
        return $this->wrapObject(function($value) use ($array) {
          if (!is_array($value) && !is_a($value,\Traversable::class))
            return false;


          return in_array(true,array_map(function($item) use ($value) {
            return in_array($item,$value,true);
          },$array),true);
        },$fieldName);
      },
      '$size' => function(int $size) use ($fieldName) {
        return $this->wrapObject(function($value) use ($size) {
          if (!is_array($value) && !is_a($value,\Countable::class))
            return false;

          return count($value) === $size;
        },$fieldName);
      }
    ];

    if (!isset($functions[$functionName]))
      throw new \InvalidArgumentException("'{$functionName}' is not a valid predicate function");
    return $functions[$functionName](...$args);
  }
}
