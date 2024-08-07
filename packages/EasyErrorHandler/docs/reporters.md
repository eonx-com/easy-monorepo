---eonx_docs---
title: 'Error reporters'
weight: 1007
---eonx_docs---

# Error reporters

The **error reporters** are used by the ErrorHandler's `report()` method to generate a report to the reporting
mechanism(s) of your choice. See [ErrorHandler](error-handler.md).

The error reporters implement `EonX\EasyErrorHandler\Common\Reporter\ErrorReporterInterface`, which defines the `report()`
method. The ErrorHandler loops through the provided error reporters and calls the `report()` method on each one.

Error reporters are provided to the ErrorHandler via implementations of
`EonX\EasyErrorHandler\Common\Provider\ErrorReporterProviderInterface`.

## Default reporters

The default error reporter logs to the main logging channel of your application.

If you use the [easy-bugsnag][1] package, then the ErrorHandler will also notify Bugsnag based on the log level of the
exception.

## Custom reporters

You can create your own custom error reporters, e.g. to send email or other notifications, and provide them to the
ErrorHandler.

Create your own custom error reporters by implementing `EonX\EasyErrorHandler\Common\Reporter\ErrorReporterInterface`.

Provide your error reporters to the ErrorHandler by using
`EonX\EasyErrorHandler\Common\Provider\ErrorReporterProviderInterface`. This interface defines the `getReporters()` method
which returns a collection of your `EonX\EasyErrorHandler\Common\Reporter\ErrorReporterInterface` implementations. The
ErrorHandler accepts a collection of all error reporter providers via its constructor.

[1]: https://packages.eonx.com/packages/easy-bugsnag/
