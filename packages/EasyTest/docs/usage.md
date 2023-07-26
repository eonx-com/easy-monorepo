---eonx_docs---
title: Usage
weight: 1002
---eonx_docs---

# Usage

The package provides tools to help you write tests for your application. The following sections describe the
available tools.

## Symfony Mailer assertions

When using `\EonX\EasyTest\Traits\MessengerAssertionsTrait::consumeAsyncMessages`, it is impossible to use
Symfony Mailer assertions. This is because the `\Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait`
uses the `\Symfony\Component\Mailer\EventListener\MessageLoggerListener` to get sent messages.

After consuming async messages, the `\Symfony\Component\Mailer\EventListener\MessageLoggerListener` is reset and
sent messages are lost.

To solve this issue use the `\EonX\EasyTest\Bridge\Symfony\Mailer\EventListener\MailerMessageLoggerListenerStub`.

### Configuration

Create or update the `config/packages/test/easy_test.php` file with the following content:

```php
<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyTestConfig;

return static function (EasyTestConfig $easyTestConfig): void {
    $easyTestConfig->mailerMessageLoggerListenerStub()
        ->enabled(true);
};
```
