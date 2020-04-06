<div align="center">
    <h1>EonX - EasyTest</h1>
    <p>Makes testing easier.</p>
</div>

---

## Documentation

### Require package (Composer)

We recommend to use [Composer][3] to manage your dependencies. You can require this package as follows:

```bash
$ composer require --dev eonx-com/easy-test
```

### Check coverage

This package provides you with a console command to check your code coverage against a limit you define. If the coverage
is lower than your limit the command fails.

This console command expect the path of a file containing the coverage output.

```bash
vendor/bin/easy-test check-coverage <path_to_coverage_output_file> --coverage=90
```

[1]: https://getcomposer.org/
