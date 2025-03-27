---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

The purpose of this package is to ease running PHP applications within a serverless context by taking care of low level
and repetitive functionalities so things should "just work".

### Require package (Composer)

The recommended way to install this package is to use [Composer][1]:

```bash
$ composer require eonx-com/easy-serverless
```

<br>

### Require dependencies

This package relies on [Bref][2] and its [Symfony Bridge][3] so make sure you do require them in your project:

```bash
$ composer require bref/bref
$ composer require bref/symfony-bridge
```

[1]: https://getcomposer.org/
[2]: https://packagist.org/packages/bref/bref
[3]: https://packagist.org/packages/bref/symfony-bridge
