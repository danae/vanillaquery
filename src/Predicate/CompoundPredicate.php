<?php
namespace Vanilla\Predicate;

abstract class CompoundPredicate implements PredicateInterface
{
  // Variables
  protected $predicates;

  // Constructor
  public function __construct(array $predicates)
  {
    if (!is_array($predicates) && !is_a($data,\Traversable::class))
      throw new \InvalidArgumentException("predicates must either be an array or implement the Traversable interface");

    foreach ($predicates as $predicate)
      if (!is_callable($predicate))
        throw new \InvalidArgumentException("All elements of predicates must be callable, found " . gettype($predicate) . " instead");

    $this->predicates = $predicates;
  }
}
