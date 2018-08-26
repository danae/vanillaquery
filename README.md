# VanillaQuery

VanillaQuery is a MongoDB-like document query interface for PHP objects.

> PLEASE NOTE: This documentation is currently not complete, it will be expanded as the code base grows. In the usage
> section you can see the basic usage of the library, but you can dive into the code and read the comments to get a
> deeper understanding of the library.

## Contents

- [Installation](#installation)
- [Usage](#usage)
- [Supported operators](#supported-operators)

<a name="installation"></a>
## Installation

Installation of VanillaQuery is best done using composer:
```bash
composer require dengsn/vanillaquery
```
Alternatively you can download the latest source code or the master branch yourself. Be sure to include the
`vendor/autoload.php` file to autoload all classes and dependencies.

<a name="usage"></a>
## Usage

```php
require("vendor/autoload.php");

// Create an example array with data
$data = [
  new Person(['firstName' => 'John', 'lastName' => 'Doe', 'age' => 43]),
  new Person(['firstName' => 'Mary', 'lastName' => 'Sue', 'age' => 21]),
  new Person(['firstName' => 'Sherlock', 'lastName' => 'Holmes', 'age' => 164]),
  new Person(['firstName' => 'John', 'lastName' => 'Watson', 'age' => 164]),
  new Person(['firstName' => 'Oliver', 'lastName' => 'Twist', 'age' => 180]),
  ...
];

// Initialize a new query object
$query = new Vanilla\Query($data);

// Return all objects with John as first name
$allJohns = $query->find(['firstName' => 'John']);

// Return everyone else
$allOthers = $query->find(['firstName' => ['$ne' => 'John']);

// Return all objects with a three letter last name
$allThreeLetterNames = $query->find(['lastName' => ['$regex' => '/[a-z]{3}/i']]);

// Return everyone older than 100
$allOldies = $query->find(['age' => ['$gt' => '100']]);

// Return the first person that is called Sherlock
$sherlock = $query->findOne(['firstName' => 'Sherlock']);

// Return the first person (in insertion order) that is between 21 and 65 years old
$adult = $query->findOne(['age' => ['$between' => [21, 65]]]);

// You can also provide queries in JSON format for brevity
// All previous queries would then be written down as:
$query->find('{"firstName": "John"}');
$query->find('{"firstName": {"$ne": "John"}}');
$query->find('{"lastName": {"$regex": "/[a-z]{3}/i"}}');
$query->find('{"age": {"$gt": "100"}}');

$query->findOne('{"firstName": "Sherlock"}');
$query->findOne('{"age": {"$between": [21, 65]}}');
```

<a name="supported-operators"></a>
## Supported operators

The following operators are supported by VanillaQuery. Most of them behave just like their MongoDB counterparts, some
are modified to provide better compatibility with the PHP language and some are new operators:

### Comparison

Name | Description
--- | ---
$eq | Matches values that are equal to a specified value
$ne | Matches all values that are not equal to a specified value
$lt | Matches values that are less than a specified value
$lte | Matches values that are less than or equal to a specified value
$gt | Matches values that are greater than a specified value
$gte | Matches values that are greater than or equal to a specified value
$between | Matches values that are in the specified range (inclusive)
$beyond | Matches values that are not in the specified range (inclusive)
$in | Matches any of the values specified in an array
$nin | Matches none of the values specified in an array

### Element

Name | Description
--- | ---
$exists | Matches objects that have the specified field
$type | Matches objects if a field is of the specified PHP type
$class | Matches objects if a fiels is an object and is of the specified class

### Evaluation

Name | Description
--- | ---
$regex | Matches objects where values match a specified regular expression

### Array

Name | Description
--- | ---
$all | Matches arrays that contain all elements specified in the query
$any | Matches arrays that contain any elements specified in the query
$size | Matches objects if the array field is a specified size
