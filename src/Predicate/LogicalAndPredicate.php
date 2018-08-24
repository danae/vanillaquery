<?php
namespace Vanilla\Predicate;

class LogicalAndPredicate extends CompoundPredicate
{
  // Return if all predicates are true for the object
  public function __invoke(object $object): bool
  {
    foreach ($this->predicates as $predicate)
      if (!$predicate($object))
        return false;
    return true;
  }
}
