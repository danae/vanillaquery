<?php
namespace Vanilla\Predicate;

interface PredicateInterface
{
  // Return if the object satisfies to the predicate
  public function __invoke(object $object): bool;
}
