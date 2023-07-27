<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Bridge\Symfony;

use Bugsnag\Client;
use Doctrine\DBAL\Configuration;
use EonX\EasyBugsnag\Bridge\Doctrine\DBAL\SqlLogger;
use EonX\EasyBugsnag\Configurators\BasicsConfigurator;
use EonX\EasyBugsnag\Configurators\RuntimeVersionConfigurator;
use EonX\EasyBugsnag\Session\SessionTracker;
use EonX\EasyBugsnag\Tests\Bridge\Symfony\Stubs\SqlLoggerStub;

final class EasyBugsnagBundleTest extends AbstractSymfonyTestCase
{
    public function testDefaultConfiguratorsOff(): void
    {
        $container = $this->getKernel([__DIR__ . '/Fixtures/default_configurators_off.yaml'])->getContainer();

        self::assertFalse($container->has(BasicsConfigurator::class));
        self::assertFalse($container->has(RuntimeVersionConfigurator::class));
    }

    public function testSanityCheck(): void
    {
        $container = $this->getKernel([__DIR__ . '/Fixtures/default_config.yaml'])->getContainer();

        self::assertInstanceOf(Client::class, $container->get(Client::class));
        self::assertInstanceOf(SessionTracker::class, $container->get(SessionTracker::class));
    }

    public function testSetSqlLoggerOnConfigNoMethodCall(): void
    {
        $container = $this->getKernel([__DIR__ . '/Fixtures/sql_logger_no_method_call.yaml'])->getContainer();

        self::assertInstanceOf(Configuration::class, $container->get('doctrine.dbal.default_connection.configuration'));
    }

    /**
     * @throws \ReflectionException
     */
    public function testSetSqlLoggerOnConfigWithMethodCall(): void
    {
        $container = $this->getKernel([__DIR__ . '/Fixtures/sql_logger_with_method_call.yaml'])->getContainer();
        /** @var \Doctrine\DBAL\Configuration $configuration */
        $configuration = $container->get('doctrine.dbal.default_connection.configuration');
        $sqlLogger = $configuration->getSQLLogger();

        self::assertInstanceOf(Configuration::class, $configuration);
        self::assertInstanceOf(SqlLogger::class, $sqlLogger);

        if ($sqlLogger instanceof SqlLogger) {
            self::assertInstanceOf(SqlLoggerStub::class, $sqlLogger->getDecorated());
        }
    }
}
