---eonx_docs---
title: Assert Logs
weight: 1002
---eonx_docs---

# Assert Logs

When your application uses [symfony/monolog-bundle][1], use `\EonX\EasyTest\Monolog\Trait\MonologTrait` to assert on
the log records produced during a test. The trait relies on `\EonX\EasyTest\Monolog\Processor\LogCollectorProcessor`,
a Monolog processor that collects every record processed by the monolog-bundle loggers (all channels) and is
registered automatically by `EasyTestBundle`.

```php
use EonX\EasyTest\Monolog\Trait\MonologTrait;
use Monolog\Level;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SomeTest extends KernelTestCase
{
    use MonologTrait;

    public function testSomething(): void
    {
        // Act ...

        self::assertLoggerHasInfo('Some message', ['some' => 'context']);
        self::assertLoggerHasWarning('Some warning');
        self::assertLoggerHasRecordMatchesRegularExpression('/Some pattern \d+/');
        self::assertLoggerHas(Level::Error, 'Some error');
    }
}
```

The collected records are reset automatically before each test (and after each test by the `EasyTestExtension`
PHPUnit extension).

> The legacy `\EonX\EasyTest\Common\Trait\LoggerTrait` and `\EonX\EasyTest\Monolog\Logger\LoggerStub` helpers cover
> applications still using the `eonx-com/easy-logging` LoggerFactory; they are deprecated and will be removed in 7.0
> together with it.

[1]: https://github.com/symfony/monolog-bundle
