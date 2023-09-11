---eonx_docs---
title: Middleware
weight: 1
---eonx_docs---

This document describes the concept of middleware and how to use them.

# What's a middleware

If you're not familiar with the Pipeline Design Pattern we recommend you to have a look at this [documentation][1].
In this package the "tasks or stages" are the middleware and are defined by the `EonX\EasyPipeline\Interfaces\MiddlewareInterface`.

<br>

# How to create a middleware

A middleware is a simple PHP object implementing the `EonX\EasyPipeline\Interfaces\MiddlewareInterface`, this
interface defines only one method `handle($input, callable $next)` where `$input` is the pipeline input data, potentially
modified by a previous middleware, and `$next` is the callable to tell the next middleware it can proceed with the input.

To ensure the pipeline works as expected, each middleware MUST keep passing the input to the next middleware. Here is
the minimum required code for a middleware:

```php
use EonX\EasyPipeline\Interfaces\MiddlewareInterface;

final class MyMiddleware implements MiddlewareInterface
{
    /**
     * Handle given input and pass return through next.
     *
     * @param callable $next
     */
     public function handle($input, callable $next)
     {
        // Do stuff here...

        return $next($input); // Return the output of the next middleware for the given input
     }
}
```

<br>

# Log each step of your pipeline

This package comes with all the tools to allow your middleware to log information for each step of your pipeline, this
can be really handy for debugging purposes. In order to allow your middleware to log information, it must implement
the `EonX\EasyPipeline\Interfaces\MiddlewareLoggerAwareInterface` so that the pipeline know your middleware
requires the `EonX\EasyPipeline\Interfaces\MiddlewareLoggerInterface` instance.

The `MiddlewareLoggerInterface` defines one method `log(string $middleware, $content): void`, the first `$middleware`
parameter is to categorise the `$content` under a single identifier, it can be any string you want.

This package provides you with the `EonX\EasyPipeline\Traits\MiddlewareLoggerAwareTrait` which defines the setter
for the `MiddlewareLoggerInterface` and also the `log($content, ?string $middleware = null)` method to easily log content.
The `$middleware` parameter is optional, when it is not set the trait will default it to your middleware class name.
The trait is a convenient way of allowing your middleware to log content but if you do not like using traits you're free
to implement the logging logic yourself.

Here is the minimum required code for your middleware to log content:

```php
use EonX\EasyPipeline\Interfaces\MiddlewareInterface;
use EonX\EasyPipeline\Interfaces\MiddlewareLoggerAwareInterface;
use EonX\EasyPipeline\Traits\MiddlewareLoggerAwareTrait;

final class MyMiddlewareWithLog implements MiddlewareInterface, MiddlewareLoggerAwareInterface
{
    use MiddlewareLoggerAwareTrait; // Will handle the setter for the MiddlewareLoggerInterface

    /**
     * Handle given input and pass return through next.
     *
     * @param $input
     * @param callable $next
     *
     * @return mixed
     */
     public function handle($input, callable $next)
     {
        // Do stuff here...

        $this->log('Content to log');

        return $next($input); // Return the output of the next middleware for the given input
     }
}
```

[1]: https://www.cise.ufl.edu/research/ParallelPatterns/PatternLanguage/AlgorithmStructure/Pipeline.htm
