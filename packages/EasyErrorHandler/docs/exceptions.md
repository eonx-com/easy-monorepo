---eonx_docs---
title: 'Exceptions'
weight: 1003
---eonx_docs---

# Exceptions

A PHP Exception contains the following properties:

- `$message`: Exception message.
- `$code`: Exception code.
- `$file`: Filename where the exception was created.
- `$line`: Line where the exception was created.

The EasyErrorHandler package provides exceptions for the most common exception use cases. Exceptions provided by the
package extend the Exception class.

## BaseException

The BaseException is the parent exception for the other EasyErrorHandler exceptions. It contains the following
additional properties:

- `$logLevel`: Log level for reporting. It can be set via the `setLogLevel()` method, or one of the following methods:
  - `setCriticalLogLevel()`
  - `setDebugLogLevel()`
  - `setErrorLogLevel()`
  - `setInfoLogLevel()`
  - `setWarningLogLevel()`
- `$statusCode`: HTTP status code for HTTP responses. It can be set via the `setStatusCode()` method.
- `$subCode`: Exception sub-code. It can be set via the `setSubCode()` method.
- `$userMessage`: User-friendly message. It can be set via the `setUserMessage()` method.

## BadRequestException

The BadRequestException can be used for 400 Bad Request HTTP responses. The response's status code is set to 400.

## ConflictException

The ConflictException can be used for 409 Conflict HTTP responses. The response's status code is set to 409.

## ErrorException

The ErrorException can be used to report any unexpected error at ERROR log level.

## ForbiddenException

The ForbiddenException can be used for 403 Forbidden HTTP responses. The response's status code is set to 403.

## NotFoundException

The NotFoundException can be used for 404 Not Found HTTP responses. The response's status code is set to 404.

## UnauthorizedException

The UnauthorizedException can be used for 401 Unauthorized HTTP responses. The response's status code is set to 401.

## ValidationException

The ValidationException extends BadRequestException with an additional list of validation errors. You can get and set
the array of errors via the `getErrors()` and `setErrors()` methods, respectively.
