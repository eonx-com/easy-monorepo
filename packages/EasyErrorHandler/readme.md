---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

This package performs the following:

- Define a common structure of exceptions
- Generate consistent error responses for unhandled exceptions within the code
- By default, log them using the main logging channel of the app
- By default, and if used with [easy-bugsnag][0] will automatically notify bugsnag when required (based on log level of the exception)

### Require package (Composer)

The recommended way to install this package is to use [Composer][1]:

```bash
$ composer require eonx-com/easy-error-handler
```

[0]: https://packages.eonx.com/projects/eonx-com/easy-bugsnag/
[1]: https://getcomposer.org/

