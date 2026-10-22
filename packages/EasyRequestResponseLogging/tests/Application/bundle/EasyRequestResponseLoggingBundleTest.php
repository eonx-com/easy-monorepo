<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Tests\Application\Bundle;

use EonX\EasyLogging\Factory\LoggerFactoryInterface;
use EonX\EasyLogging\Logger\LazyLogger;
use EonX\EasyRequestResponseLogging\Bundle\Enum\BundleParam;
use EonX\EasyRequestResponseLogging\Common\Subscriber\RequestResponseLoggingSubscriber;
use EonX\EasyRequestResponseLogging\Tests\Application\AbstractApplicationTestCase;
use EonX\EasyTest\Monolog\Logger\LoggerStub;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\HttpFoundation\Request;

final class EasyRequestResponseLoggingBundleTest extends AbstractApplicationTestCase
{
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

    public function testSubscriberUsesEasyLoggingLoggerWhenEasyLoggingBundleIsEnabled(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel([
            'environment' => 'test_easy_logging',
        ]);
        $request = Request::create('/response');

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        /** @var \EonX\EasyLogging\Factory\LoggerFactoryInterface $loggerFactory */
        $loggerFactory = self::getContainer()->get(LoggerFactoryInterface::class);
        $logger = $loggerFactory->create(BundleParam::LogChannel->value);
        if ($logger instanceof LazyLogger) {
            $logger = $logger->getLogger();
        }
        self::assertInstanceOf(LoggerStub::class, $logger);
        self::assertTrue($logger->hasInfo('Request: "GET /response" -> 200'));
    }

    public function testThrowsWhenEasyRequestIdBundleIsNotEnabled(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('EasyRequestIdBundle is not enabled.');

        self::bootKernel([
            'environment' => 'test_easy_request_id_disabled',
        ]);
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
