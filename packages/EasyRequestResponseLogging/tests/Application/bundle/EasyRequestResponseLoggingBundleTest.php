<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Tests\Application\Bundle;

use EonX\EasyRequestResponseLogging\Common\Listener\RequestResponseLoggingListener;
use EonX\EasyRequestResponseLogging\Tests\Application\AbstractApplicationTestCase;
use Symfony\Component\DependencyInjection\Exception\LogicException;

final class EasyRequestResponseLoggingBundleTest extends AbstractApplicationTestCase
{
    public function testListenerIsNotRegisteredByDefault(): void
    {
        self::bootKernel([
            'environment' => 'test_disabled',
        ]);

        self::assertFalse(self::getContainer()->has(RequestResponseLoggingListener::class));
    }

    public function testListenerIsRegisteredWhenEnabled(): void
    {
        self::bootKernel();

        self::assertTrue(self::getContainer()->has(RequestResponseLoggingListener::class));
    }

    public function testThrowsWhenSensitiveDataSanitizerIsDisabled(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('sensitive data sanitizer must not be disabled');

        self::bootKernel([
            'environment' => 'test_sanitizer_disabled',
        ]);
    }
}
