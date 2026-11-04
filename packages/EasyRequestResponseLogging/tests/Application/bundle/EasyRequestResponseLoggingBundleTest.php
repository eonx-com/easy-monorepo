<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Tests\Application\Bundle;

use EonX\EasyRequestResponseLogging\Common\Subscriber\RequestResponseLoggingSubscriber;
use EonX\EasyRequestResponseLogging\Tests\Application\AbstractApplicationTestCase;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

final class EasyRequestResponseLoggingBundleTest extends AbstractApplicationTestCase
{
    public function testBootKernelThrowsExceptionWhenEasyRequestIdBundleIsNotEnabled(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('EasyRequestIdBundle is not enabled.');

        self::bootKernel([
            'environment' => 'test_easy_request_id_disabled',
        ]);
    }

    public function testBootKernelThrowsExceptionWhenSensitiveDataSanitizerIsDisabled(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SensitiveDataSanitizerInterface" but no such service exists');

        self::bootKernel([
            'environment' => 'test_sanitizer_disabled',
        ]);
    }

    public function testSubscriberIsNotRegisteredByDefault(): void
    {
        self::bootKernel([
            'environment' => 'test_disabled',
        ]);

        self::assertFalse(self::getContainer()->has(RequestResponseLoggingSubscriber::class));
    }

    public function testSubscriberIsRegisteredWhenEnabled(): void
    {
        self::bootKernel();

        self::assertTrue(self::getContainer()->has(RequestResponseLoggingSubscriber::class));
    }
}
