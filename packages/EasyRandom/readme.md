---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

Do you need to generate random and unique values? This package is for you!

- Strings
- Numbers
- UUIDs

All the randomness you need!

### Require package (Composer)

The recommended way to install this package is to use [Composer][1]:

```bash
$ composer require eonx-com/easy-random
```

### Usage

#### Integers

```php
// Will generate a random integer between 0 and 20 (both included)
$myNumber = (new \EonX\EasyRandom\RandomGenerator())->randomInteger(0, 20);
```

### Strings

The random generator allows you to control the length, and the composition of the generated random strings via a nice
fluent interface:

```php
$myString = (new \EonX\EasyRandom\RandomGenerator())
    ->randomString(16)
    ->excludeSimilar() // Will exclude similar characters
    ->excludeVowel() // Will exclude vowels, nice trick to avoid "bad words" in generated random strings
    ->includeNumeric(); // Include 0-9 numbers
```

Do you need to generate random strings for your end users?

```php
// Will generate "user friendly" random string:
// - exclude ambiguous characters
// - exclude symbols
// - exclude vowels
// - include numeric
// - include uppercase

$reference = (new \EonX\EasyRandom\RandomGenerator())
    ->randomString(16)
    ->userFriendly();
```

### UUID V4

The random generator allows you to generate UUID V4, to do so, you will need to set the UUID V4 generator of your choice
onto the random generator instance. 
This package comes with built-in implementations for: [ramsey/uuid][3], [symfony/uid][2].
If you want to use your own, then you will need to make sure it implements `EonX\EasyRandom\Interfaces\UuidV4GeneratorInterface`.

```php

$uuidV4Generator = new \EonX\EasyRandom\UuidV4\RamseyUuidV4Generator();
$randomGenerator = (new \EonX\EasyRandom\RandomGenerator())->setUuidV4Generator($uuidV4Generator);

$uuidV4 = $randomGenerator->uuidV4();
```

#### In Symfony configure your preferred UUID V4 generator

To make this possible, the bundle will register the built-in generators as services.

- *symfony_uuid4* alias for `EonX\EasyRandom\UuidV4\SymfonyUidUuidV4Generator`
- *ramsey_uuid4* alias for `EonX\EasyRandom\UuidV4\RamseyUuidV4Generator`

```yaml
easy_random:
    uuid_v4_generator: 'easy_random.symfony_uuid4'
```

[1]: https://getcomposer.org/
[2]: https://symfony.com/doc/current/components/uid.html
[3]: https://github.com/ramsey/uuid
