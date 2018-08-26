<?php
namespace Vanilla;

use Vanilla\Evaluator\Evaluator;
use Vanilla\Evaluator\EvaluatorInterface;

class Query
{
  // Variables
  public $data;
  private $evaluator;

  // Constructor
  public function __construct(&$data, EvaluatorInterface $evaluator = null)
  {
    if (!is_array($data) && !is_a($data,\Traversable::class))
      throw new \InvalidArgumentException("data must be either an array or implement the Traversable interface");

    $this->data = $data;
    $this->evaluator = $evaluator ?? new Evaluator();
  }

  // Evaluate a query array
  public function evaluate($query)
  {
    if (is_string($query))
      $query = JsonDecoder::decode($query);
    elseif (!is_array($query))
      throw new \InvalidArgumentException("query must be either an array or a string containing valid JSON");

    return $this->evaluator->evaluate($query);
  }

  // Return the found results of a query
  public function find($query): array
  {
    // Evaluate the query
    $predicate = $this->evaluate($query);

    // Create a new array to store the results
    $results = [];

    // Iterate over the data
    foreach ($this->data as $key => $object)
    {
      // Check if the object satisfies the predicate and add it to the results if so
      if ($predicate($object))
        $results[$key] = $object;
    }

    // Return the results
    return $results;
  }

  // Return the first found result of a query or null if nothing found
  public function findOne($query): ?object
  {
    // Evaluate the query
    $predicate = $this->evaluate($query);

    // Iterate over the data
    foreach ($this->data as $key => $object)
    {
      // Check if the object satisfies the predicate and return it if so
      if ($predicate($object))
        return $object;
    }

    // Nothing found
    return null;
  }
}
