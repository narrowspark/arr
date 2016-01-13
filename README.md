
A PHP collection of utilities to manipulate arrays. Compatible with PHP 5.6+, PHP 7, and HHVM.

[![Author](http://img.shields.io/badge/author-@anolilab-blue.svg?style=flat-square)](https://twitter.com/anolilab)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/narrowspark/arr.svg?style=flat-square)](https://packagist.org/packages/narrowspark/arr)
[![Total Downloads](https://img.shields.io/packagist/dt/narrowspark/arr.svg?style=flat-square)](https://packagist.org/packages/narrowspark/arr)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

## Master

[![Build Status](https://img.shields.io/travis/narrowspark/arr/master.svg?style=flat-square)](https://travis-ci.org/narrowspark/arr)
[![Quality Score](https://img.shields.io/scrutinizer/g/narrowspark/arr.svg?style=flat-square)](https://scrutinizer-ci.com/g/narrowspark/arr)

## Develop

[![Build Status](https://img.shields.io/travis/narrowspark/arr/master.svg?style=flat-square)](https://travis-ci.org/narrowspark/arr)
[![Quality Score](https://img.shields.io/scrutinizer/g/narrowspark/arr.svg?style=flat-square)](https://scrutinizer-ci.com/g/narrowspark/arr)

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
``` php

```

#### get
``` php

```

#### add
``` php

```

#### has
``` php

```

#### update
``` php

```

#### forget
``` php

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
