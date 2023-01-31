---eonx_docs---
title: Usage
weight: 1002
---eonx_docs---

# Usage

The package provides tools to help you write tests for your application. The following sections describe the
available tools.

## Symfony Mailer assertions

When using `\EonX\EasyTest\Traits\MessengerAssertionsTrait::consumeAsyncMessages`, it impossible to use
Symfony Mailer assertions. This is because the `\Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait`
uses the `\Symfony\Component\Mailer\EventListener\MessageLoggerListener` to get sent messages.

After consuming async messages, the `\Symfony\Component\Mailer\EventListener\MessageLoggerListener` is reset and
sent messages are lost.

To solve this issue, it is possible to use the `\EonX\EasyTest\Bridge\Symfony\Mailer\EventListener\MessageLoggerListenerStub`.

### Configuration

Create or update the `config/packages/test/easy_test.php` file with the following content:

```php
<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyTestConfig;

return static function (EasyTestConfig $easyTestConfig): void {
    $easyTestConfig->messageLoggerListenerStubEnabled(true);
};
```

Update the PHPUnit configuration file to use `MessageLoggerListenerPHPUnitExtension`:

```xml

<phpunit>
    <!-- ... -->
    <extensions>
        <!-- ... -->
        <extension class="EonX\EasyTest\Bridge\PhpUnit\Extension\MessageLoggerListenerPHPUnitExtension"/>
    </extensions>
</phpunit>
```

#### Manual configuration

It is also possible to configure the `MessageLoggerListenerStub` manually.

We don't need the `kernel.reset` tag, which is added by default, because we don't need to reset the service.
So we override the service instead of decorating.

Register the `MessageLoggerListenerStub` in the `config/services_test.php` file:

```php
    $services->set('mailer.message_logger_listener', MessageLoggerListenerStub::class)
        ->tag('kernel.event_subscriber');
```

Reset the `MessageLoggerListenerStub` in a test case.

We need to reset stabs exactly in the setUp method because when some test fails,
the stubs will continue holding stubbed values, and the next test will fail.

As a result, we will have many failed tests, and it may be complicated to find the wrong test,
and it will be, for sure, hard to check test execution logs.

```php
<?php
declare(strict_types=1);

namespace Test;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use EonX\EasyTest\Bridge\Symfony\Mailer\EventListener\MessageLoggerListenerStub;

abstract class AbstractTestCase extends KernelTestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        MessageLoggerListenerStub::reset();
    }

    ...
```
