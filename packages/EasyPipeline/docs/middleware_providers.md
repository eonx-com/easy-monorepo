---eonx_docs---
title: Middleware Providers
weight: 2
---eonx_docs---

This document describes the concept of middleware providers and how to use them.

# What's a pipeline?

If you're not familiar with the Pipeline Design Pattern we recommend you to have a look at this [documentation][1].
In this package the "tasks or stages" are represented by the `EonX\EasyPipeline\Interfaces\MiddlewareInterface`.
So the pipelines created using this package will allow you to process input data through a collection of middleware.
The only way to define the middleware each pipeline will use is via `EonX\EasyPipeline\Interfaces\MiddlewareProviderInterface`.

<br>

# How MiddlewareProviders work?

Each middleware provider must be an instance of the `EonX\EasyPipeline\Interfaces\MiddlewareProviderInterface`,
this interface defines one simple method `getMiddlewareList(): array`. The objective of this method is to return a list
of middleware to use within a pipeline instance.

<br>

## What values can my MiddlewareProviders return?

The middleware providers must return an array of `MiddlewareInterface`.

That's it!? That's all!? Not really handy, I'm sure we can do better!

Yes you're right, this package comes with an implementation for any application using a PSR Service Container!
That means you can also register your middleware inside the service container and return its service locator from your
MiddlewareProvider and this package will know how to instantiate it! We recommend you to register your middleware using
its [FQCN][2].

<br>

### Illuminate Pipeline implementation

The [Illuminate Pipeline][3] knows how to resolve middleware using the PSR Service Container as well, and on the top of
that it also allows you to define your middleware using `callable`. So if you're a using the `IlluminatePipeline`
implementation of this package your MiddlewareProviders can return closures and any kind of `callable` as well.

<br>

#### Accessing the Pipeline name

If you're using the IlluminatePipelineFactory, a MiddlewareProvider can access the name it was registered under by implementing the `EonX\EasyPipeline\Interfaces\PipelineNameAwareInterface`.
The factory will call `setPipelineName()` on any MiddlewareProviders implementing this.

To save time, the `EonX\EasyPipeline\Traits\PipelineNameAwareTrait` is available, providing an implementation of this function and the private property `$pipelineName`.

<br>

# Dependency Injection

This package is using the PSR Service Container to instantiate your MiddlewareProviders which means you can use
dependency injection on them, how convenient!

<br>

# Example

```php
use App\Validator\MyValidatorInterface;
use EonX\EasyPipeline\Interfaces\MiddlewareProviderInterface;

final class MyPipeline1MiddlewareProvider implements MiddlewareProviderInterface
{
    /**
     * @var App\Validator\MyValidatorInterface
     */
    private $validator;

    public function __construct(MyValidatorInterface $validator)
    {
        $this->validator = $validator; // You can use dependency injection
    }

    /**
     * Get middleware list, middleware could be anything your container can resolve.
     */
     public function getMiddlewareList(): array
     {
         return [
            new ChangeNameMiddleware(), // Instance of the middleware
            ChangeAddressMiddleware::class, // FQCN of the middleware to be resolve by the service container
            new MyValidatorMiddleware($this->validator), // Instance of the middleware with dependency injection

            // If you're using the IlluminatePipeline implementation
            function ($input, $next) {
                // Do stuff here...

                return $next($input);
            },

            // Or even
            [$this, 'actAsMiddleware']
         ];
     }

     /**
      * This method will act as a middleware.
      *
      * @param \Closure $next
      */
     public function actAsMiddleware($input, \Closure $next)
     {
        // Do stuff here...

        return $next($input);
     }
}
```

[1]: https://www.cise.ufl.edu/research/ParallelPatterns/PatternLanguage/AlgorithmStructure/Pipeline.htm

[2]: https://en.wikipedia.org/wiki/Fully_qualified_name

[3]: https://packagist.org/packages/illuminate/pipeline
