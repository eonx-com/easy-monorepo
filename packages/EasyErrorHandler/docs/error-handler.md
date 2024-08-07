---eonx_docs---
title: ErrorHandler
weight: 1004
---eonx_docs---

# ErrorHandler

The **ErrorHandler** class provides the main methods to build HTTP responses for exceptions and to report on exceptions
(e.g. logging).

## Methods

### Render method

The `render()` method is responsible for building an informative HTTP response when an exception occurs.

The method accepts the HTTP request and the exception. It loops through the
**[error response builders](response-builders.md)** that have been provided to the ErrorHandler in order to generate the
HTTP response.

### Report method

The `report()` method is responsible for reporting exceptions to the reporting mechanism(s) of your choice.

The method accepts the exception and generates a report for each **[error reporter](reporters.md)** that has been
provided to the ErrorHandler.

The default error reporter logs to the main logging channel of your application, but you can implement additional error
reporters, e.g. to send email.

If you use the [easy-bugsnag][1] package, then the ErrorHandler will also notify Bugsnag based on the log level of the
exception.

[1]: https://packages.eonx.com/packages/easy-bugsnag/
