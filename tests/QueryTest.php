<?php
namespace Vanilla\Tests;

use PHPUnit\Framework\TestCase;
use Vanilla\Query;
use Vanilla\Predicate\PredicateInterface;
use Vanilla\Tests\Objects\Address;
use Vanilla\Tests\Objects\Person;
use Vanilla\Tests\Objects\PersonWithBirthDate;

class QueryTest extends TestCase
{
  public function dataProvider()
  {
    return [
      'john_doe' => new Person([
        'firstName' => 'John',
        'lastName' => 'Doe',
        'address' => new Address(['city' => 'New York', 'country' => 'US']),
        'tags' => null,
        'rank' => 12
      ]),
      'mary_sue' => new Person([
        'firstName' => 'Mary',
        'lastName' => 'Sue',
        'address' => new Address(['city' => 'Los Angeles', 'country' => 'US']),
        'tags' => ['fictional'],
        'rank' => 11
      ]),
      'sherlock_holmes' => new Person([
        'firstName' => 'Sherlock',
        'lastName' => 'Holmes',
        'address' => new Address(['city' => 'London', 'country' => 'GB']),
        'tags' => ['detective', 'fictional'],
        'rank' => 99,
        'born' => new \DateTime('1854-01-06')
      ]),
      'john_watson' => new Person([
        'firstName' => 'John',
        'lastName' => 'Watson',
        'address' => new Address(['city' => 'London', 'country' => 'GB']),
        'tags' => ['medic', 'fictional'],
        'rank' => 98
      ]),
      'raja_patil' => new Person([
        'firstName' => 'Raja',
        'lastName' => 'Patil',
        'address' => new Address(['city' => 'New Delhi', 'country' => 'IN']),
        'tags' => ['microsoft'],
        'rank' => 50
      ])
    ];
  }

  public function queryFindProvider()
  {
    return [
      // Comparison functions
      '$eq' => [['firstName' => ['$eq' => 'John']], ['john_doe', 'john_watson']],
      '$ne' => [['firstName' => ['$ne' => 'John']], ['mary_sue', 'sherlock_holmes', 'raja_patil']],
      '$lt' => [['rank' => ['$lt' => 12]], ['mary_sue']],
      '$lte' => [['rank' => ['$lte' => 12]], ['john_doe', 'mary_sue']],
      '$gt' => [['rank' => ['$gt' => 50]], ['sherlock_holmes', 'john_watson']],
      '$gte' => [['rank' => ['$gte' => 50]], ['sherlock_holmes', 'john_watson', 'raja_patil']],

      // Range comparison functions
      '$between' => [['rank' => ['$between' => [12,50]]], ['john_doe', 'raja_patil']],
      '$beyond' => [['rank' => ['$beyond' => [12,50]]], ['mary_sue', 'sherlock_holmes', 'john_watson']],

      // Multiple comparison functions
      '$in' => [['lastName' => ['$in' => ['Holmes', 'Watson']]], ['sherlock_holmes', 'john_watson']],
      '$nin' => [['address.country' => ['$nin' => ['US', 'IN']]], ['sherlock_holmes', 'john_watson']],

      // Element functions
      '$exists true' => [['born' => ['$exists' => true]], ['sherlock_holmes']],
      '$exists true with null' => [['tags' => ['$exists' => true]], ['john_doe', 'mary_sue', 'sherlock_holmes', 'john_watson', 'raja_patil']],
      '$exists false' => [['born' => ['$exists' => false]], ['john_doe', 'mary_sue', 'john_watson', 'raja_patil']],
      '$exists false with null' => [['tags' => ['$exists' => false]], []],
      '$type integer' => [['rank' => ['$type' => 'integer']], ['john_doe', 'mary_sue', 'sherlock_holmes', 'john_watson', 'raja_patil']],
      '$type string' => [['address.city' => ['$type' => 'string']], ['john_doe', 'mary_sue', 'sherlock_holmes', 'john_watson', 'raja_patil']],
      '$type array' => [['tags' => ['$type' => 'array']], ['mary_sue', 'sherlock_holmes', 'john_watson', 'raja_patil']],
      '$type object' => [['address' => ['$type' => 'object']], ['john_doe', 'mary_sue', 'sherlock_holmes', 'john_watson', 'raja_patil']],
      '$class Address' => [['address' => ['$class' => Address::class]], ['john_doe', 'mary_sue', 'sherlock_holmes', 'john_watson', 'raja_patil']],
      '$class DateTime' => [['born' => ['$class' => \DateTime::class]], ['sherlock_holmes']],

      // Array functions
      '$all' => [['tags' => ['$all' => ['fictional', 'medic']]], ['john_watson']],
      '$any' => [['tags' => ['$any' => ['fictional', 'medic']]], ['mary_sue', 'sherlock_holmes', 'john_watson']],
      '$size' => [['tags' => ['$size' => 1]], ['mary_sue', 'raja_patil']],
      '$size 0' => [['tags' => ['$size' => 0]], []],

      // Evaluator functions
      '$regex' => [['address.city' => ['$regex' => '/^L.+$/']], ['mary_sue', 'sherlock_holmes', 'john_watson']],

      // Other tests
      'implicit' => [['firstName' => 'John'], ['john_doe', 'john_watson']],
      'multiple' => [['firstName' => 'John', 'rank' => ['$lt' => 50]], ['john_doe']],

      // Some tests with JSON queries
      'json $lt' => ['{"rank": {"$lt": 12}}', ['mary_sue']],
      'json $in' => ['{"lastName": {"$in": ["Holmes", "Watson"]}}', ['sherlock_holmes', 'john_watson']],
      'json $class DateTime' => ['{"born": {"$class": "' . \DateTime::class . '"}}', ['sherlock_holmes']],
      'json $size' => ['{"tags": {"$size": 1}}', ['mary_sue', 'raja_patil']]
    ];
  }

  public function testConstructor()
  {
    $data = $this->dataProvider();
    $queryObject = new Query($data);

    $this->assertInstanceOf(Query::class,$queryObject);
    $this->assertAttributeEquals($data,'data',$queryObject);

    return $queryObject;
  }

  /**
  * @dataProvider queryFindProvider
  * @depends testConstructor
  */
  public function testFind($query, array $expectedKeys, Query $queryObject)
  {
    $results = $queryObject->find($query);

    $this->assertEquals($expectedKeys,array_keys($results));
    foreach ($expectedKeys as $expectedKey)
      $this->assertEquals($queryObject->data[$expectedKey],$results[$expectedKey]);
  }

  /**
  * @dataProvider queryFindProvider
  * @depends testConstructor
  */
  public function testFindOne($query, array $expectedKeys, Query $queryObject)
  {
    $result = $queryObject->findOne($query);

    if (empty($expectedKeys))
    {
      $this->assertEquals(null,$result);
    }
    else
    {
      reset($expectedKeys);
      $expectedKey = current($expectedKeys);
      $this->assertEquals($queryObject->data[$expectedKey],$result);
    }
  }
}
