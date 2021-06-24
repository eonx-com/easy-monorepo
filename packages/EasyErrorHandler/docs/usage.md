---eonx_docs---
title: Usage
weight: 1002
---eonx_docs---

# Usage

When using this package with your favourite framework, `\EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface` is
registered as a service, so you can use dependency injection to use it within your application. For example:

```php
// src/Service/MyService.php

namespace App\Service;

use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;

final class MyService
{
    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface
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

## Symfony

Due to the [Autowiring via setters][1] feature of Symfony, you can use
`\EonX\EasyErrorHandler\Traits\ErrorHandlerAwareTrait` to simplify the injection of
`\EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface`.

[1]: https://symfony.com/doc/current/service_container/autowiring.html#autowiring-calls
