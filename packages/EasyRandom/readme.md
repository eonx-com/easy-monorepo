---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

Do you need to generate random and unique values? This package is for you!

- Strings
- Numbers
- UUIDs

All the randomness you need!

<br>

### Require package (Composer)

The recommended way to install this package is to use [Composer][1]:

```bash
$ composer require eonx-com/easy-random
```

<br>

### Usage

#### Integers

```php
// Will generate a random integer between 0 and 20 (both included)
$myNumber = (new \EonX\EasyRandom\Generator\RandomGenerator(...))->integer(0, 20);
```

<br>

### Strings

The random generator allows you to control the length, and the composition of the generated random strings via a nice
fluent interface:

```php
$myString = (new \EonX\EasyRandom\Generator\RandomGenerator(...))
    ->string(16)
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

$reference = (new \EonX\EasyRandom\Generator\RandomGenerator(...))
    ->string(16)
    ->userFriendly();
```

<br>

### UUID

The random generator allows you to generate UUID.
This package comes with built-in implementations for: [ramsey/uuid][3], [symfony/uid][2].
If you want to use your own, then you will need to make sure it implements `EonX\EasyRandom\Generator\UuidGeneratorInterface`.

```php
$uuid = (new \EonX\EasyRandom\Generator\RandomGenerator(...))->uuid();
```

[1]: https://getcomposer.org/

[2]: https://symfony.com/doc/current/components/uid.html

[3]: https://github.com/ramsey/uuid
