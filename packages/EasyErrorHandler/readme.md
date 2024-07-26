---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

# Introduction

The **EasyErrorHandler** package allows you to easily build informative HTTP responses and reports in response to
exceptions in your applications.

Using the EasyErrorHandler package, you can:

- Define a common structure for exceptions
- Generate consistent error responses for unhandled exceptions within your code
- Report exceptions to the main logging channel of your application (by default) or to custom destinations
- Automatically notify Bugsnag based on the log level of the exception (by default, if used with [easy-bugsnag][1])

## Usage overview

The package will work with any PHP exception, but it provides several tailored **[Exceptions](exceptions.md)** for
common error conditions, e.g. 404 Not Found errors.

The **[ErrorHandler](error-handler.md)** provides the main methods to build HTTP responses for exceptions and to report
on exceptions (e.g. logging).

HTTP responses are built with **[error response builders](response-builders.md)**. These builders progressively build
the response body, status code and headers. You can implement your own error response builders. The HTTP response body
is formatted as JSON by default, but you can implement your own formatter (see [Response format](response-format.md)).

**[Error reporters](reporters.md)** report on exceptions. The default error reporter logs to the main logging channel of
your application, but you can implement custom error reporters, e.g. to send email.

[1]: https://packages.eonx.com/packages/easy-bugsnag/
