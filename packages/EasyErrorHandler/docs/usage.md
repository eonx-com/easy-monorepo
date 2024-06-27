---eonx_docs---
title: Usage
weight: 1002
---eonx_docs---

# Usage

The EasyErrorHandler package seamlessly integrates with your favourite framework. Once the package has been installed
and enabled, all exceptions handled by your framework will use the EasyErrorHandler package to generate error responses
and reports.

## Using explicitly

You can also use the package to explicitly report an exception at any point in your application. Since
`\EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface` is registered as a service in your framework, you can use
dependency injection to use it within your application. For example:

```php
// src/Service/MyService.php

namespace App\Service;

use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;

final class MyService
{
    /**
     * @var \EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface
     */
    private $errorHandler;

    public function __construct(ErrorHandlerInterface $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    public function doSomething(): void
    {
        try {
            // Do something...
        } catch (\Throwable $throwable) {
            $this->errorHandler->report($throwable);
        }
    }
}
```

### Symfony

Due to the [Autowiring via setters][1] feature of Symfony, you can use
`\EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerAwareTrait` to simplify the injection of
`\EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface`.

[1]: https://symfony.com/doc/current/service_container/autowiring.html#autowiring-calls
