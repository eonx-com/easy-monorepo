---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

### Require package (Composer)

We recommend to use [Composer][1] to manage your dependencies. You can require this package as follows:

```bash
$ composer require --dev eonx-com/easy-test
```

<br>

### Check coverage

This package provides you with a console command to check your code coverage against a limit you define. If the coverage
is lower than your limit the command fails.

This console command expect the path of a file containing the coverage output.

```bash
vendor/bin/easy-test easy-test:check-coverage <path_to_coverage_output_file> --coverage=90
```

[1]: https://getcomposer.org/
