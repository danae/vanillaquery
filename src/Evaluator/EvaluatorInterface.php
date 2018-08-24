<?php
namespace Vanilla\Evaluator;

use Vanilla\Predicate\PredicateInterface;

interface EvaluatorInterface
{
  // Evaluate a query array into a predicate
  public function evaluate(array $query): callable;
}
