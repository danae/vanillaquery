<?php
namespace Vanilla\Predicate;

class LogicalOrPredicate extends CompoundPredicate
{
  // Return if any predicate is true for the object
  public function __invoke($object): bool
  {
    foreach ($this->predicates as $predicate)
      if ($predicate($object))
        return true;
    return false;
  }
}
