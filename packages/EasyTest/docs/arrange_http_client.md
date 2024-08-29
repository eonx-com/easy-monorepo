---eonx_docs---
title: Arrange HTTP Client
weight: 1001
---eonx_docs---

# Arrange HTTP Client

Add `\EonX\EasyTest\HttpClient\Factory\TestResponseFactory` and `\EonX\EasyTest\EasyErrorHandler\ErrorHandler\TraceableErrorHandlerStub`
to you test services.

```php
<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyErrorHandler\Common\ErrorHandler\TraceableErrorHandlerInterface;
use EonX\EasyTest\EasyErrorHandler\ErrorHandler\TraceableErrorHandlerStub;
use EonX\EasyTest\HttpClient\Factory\TestResponseFactory;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(TestResponseFactory::class);

    $services->set(TraceableErrorHandlerStub::class)
        ->decorate(TraceableErrorHandlerInterface::class)
        ->arg('$decorated', service('.inner'));
};
```

Configure Symfony Http Client to use `TestResponseFactory` in your test environment.

```php
// config/packages/test/http_client.php

<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyTest\HttpClient\Factory\TestResponseFactory;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $frameworkConfig): void {
    $frameworkConfig->httpClient()
        ->mockResponseFactory(TestResponseFactory::class);
};
```

Register `EonX\EasyTest\PHPUnit\Extension\EasyTestExtension` in PHPUnit configuration.

```xml
<!-- phpunit.xml.dist -->
    ...
<extensions>
    <bootstrap class="EonX\EasyTest\PHPUnit\Extension\EasyTestExtension"/>
</extensions>
    ...
```

Use arrange methods from `\EonX\EasyTest\HttpClient\Trait\HttpClientApplicationTestTrait`
or `\EonX\EasyTest\HttpClient\Trait\HttpClientUnitTestTrait` in your tests.
