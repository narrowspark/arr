
A PHP collection of utilities to manipulate arrays. Compatible with PHP 5.6+, PHP 7, and HHVM.

[![Author](http://img.shields.io/badge/author-@anolilab-blue.svg?style=flat-square)](https://twitter.com/anolilab)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/narrowspark/arr.svg?style=flat-square)](https://packagist.org/packages/narrowspark/arr)
[![Total Downloads](https://img.shields.io/packagist/dt/narrowspark/arr.svg?style=flat-square)](https://packagist.org/packages/narrowspark/arr)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

## Master

[![Build Status](https://img.shields.io/travis/narrowspark/arr/master.svg?style=flat-square)](https://travis-ci.org/narrowspark/arr)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/narrowspark/arr/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/narrowspark/arr/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/narrowspark/arr/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/narrowspark/arr)

## Develop

[![Build Status](https://img.shields.io/travis/narrowspark/arr/develop.svg?style=flat-square)](https://travis-ci.org/narrowspark/arr)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/narrowspark/arr/develop.svg?style=flat-square)](https://scrutinizer-ci.com/g/narrowspark/arr/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/narrowspark/arr/develop.svg?style=flat-square)](https://scrutinizer-ci.com/g/narrowspark/arr)

* [Why?](#why)
* [Installation](#installation)
* [Arr and StaticArr](#arr-and-staticarr)
* [Instance methods](#instance-methods)
    * [Access methods](#access)
        * [set](#set)
        * [get](#get)
        * [add](#add)
        * [has](#has)
        * [update](#update)
        * [forget](#forget)
    * [Enumerator](#enumerator)
        * [random](#random)
        * [only](only)
        * [split](#split)
        * [isAssoc](#isAssoc)
        * [isIndexed](#isIndexed)
    * [Transform](#transform)
        * [pop](#pop)
        * [swap](#swap)
        * [every](#every)
        * [combine](#combine)
        * [collapse](#collapse)
        * [divide](#divide)
        * [stripEmpty](#stripEmpty)
        * [unique](#unique)
        * [without](#without)
        * [reindex](#reindex)
        * [merger](#merge)
        * [extend](#extend)
        * [asHierarchy](#asHierarchy)
        * [groupBy](#groupBy)
        * [dot](#dot)
        * [flatten](#flatten)
        * [expand](#expand)
        * [reset](#reset)
        * [extendDistinct](#extendDistinct)
        * [sortRecursive](#sortRecursive)
        * [zip](#zip)
    * [Traverse](#traverse)
        * [map](#map)
        * [filter](#filter)
        * [all](#all)
        * [reject](#reject)
        * [where](#where)
        * [first](#first)
        * [last](#last)

## Why?

## Installation

Via Composer

``` bash
$ composer require narrowspark/arr
```

or

``` json
"require": {
    "narrowspark/arr": "~1.0"
}
```

## Arr and StaticArr

All methods listed under "Instance methods" are available as part of a wrapper.

``` php
use Narrowspark\Arr\Arr;
use Narrowspark\Arr\StaticArr;

// Translates to Access::set(['foo' => bar], 'arr', 'narrowspark');
// Returns a new array with the added key and value;
// ['foo' => bar, 'arr' => 'narrowsaprk']
(new Arr())->set(['foo' => bar], 'arr', 'narrowspark');
// or you can make a static call
StaticArr::set(['foo' => bar], 'arr', 'narrowspark');
```

## Instance Methods

### Access

#### set

Set an array item to a given value using "dot" notation. If no key is given to the method, the entire array will be replaced.
``` php
    $arr = StaticArr::set(['foo' => 'bar'], 'arr', 'narrowspark'); // ['foo' => 'bar', 'arr' => 'narrowspark']
```

#### get

Get an item from an array using "dot" notation. If key dont exist, you get a default value back.

``` php
    $arr = StaticArr::get(['foo' => 'bar'], 'foo', 'narrowspark'); // bar
```

#### add

Add an element to the array at a specific location using the "dot" notation.

``` php
    $arr = StaticArr::add(['foo' => 'bar'], 'foo', 'narrowspark'); // ['foo' => ['bar', 'narrowspark']]
```

#### has

Check if an item exists in an array using "dot" notation.

``` php
    $arr = StaticArr::has(['foo' => 'bar'], 'foo'); // true
```

#### update

Updates data at the given path.

``` php
    $increment = function ($value) {
        return $value + 1;
    };

    $arr = StaticArr::update(['foo' => 1], $increment); // ['foo' => 2]
```

#### forget

Remove one or many array items from a given array using "dot" notation.

``` php
    $arr = StaticArr::forget(['foo' => 'bar'], 'foo'); // []
```

### Enumerator

### Transform

### Traverse

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

From the project directory, tests can be ran using phpunit

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email d.bannert@anolilab.de instead of using the issue tracker.

## Credits

- [Daniel Bannert](https://github.com/prisis)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
