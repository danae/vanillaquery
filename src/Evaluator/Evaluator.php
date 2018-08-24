<?php
namespace Vanilla\Evaluator;

use Vanilla\Predicate\LogicalAndPredicate;
use Vanilla\Predicate\LogicalOrPredicate;

class Evaluator implements EvaluatorInterface
{
  // Variables
  private $functions;

  // Constructor
  public function __construct()
  {
    $this->functions = new Functions();
  }

  // Evaluate a query array into a predicate
  public function evaluate(array $query): callable
  {
    $predicates = [];

    foreach ($query as $fieldName => $value)
    {
      // If the value is no array, then assume equality check
      if (!is_array($value))
        $predicates[] = $this->functions->fn('$eq',$fieldName,$value);

      // If it is an array, then assume the key as function and value as arguments to the constructor
      else
      {
        reset($value);
        $functionName = key($value);
        $args = current($value);
        if (!is_array($args))
          $args = [$args];

        $predicates[] = $this->functions->fn($functionName,$fieldName,...$args);
      }
    }

    return (count($predicates) === 1) ? current($predicates) : new LogicalAndPredicate($predicates);
  }
}
