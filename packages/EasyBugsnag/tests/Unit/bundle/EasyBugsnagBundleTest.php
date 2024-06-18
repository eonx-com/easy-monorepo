<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Unit\Bundle;

use Bugsnag\Client;
use Doctrine\DBAL\Configuration;
use EonX\EasyBugsnag\Configurator\BasicsClientConfigurator;
use EonX\EasyBugsnag\Configurator\RuntimeVersionClientConfigurator;
use EonX\EasyBugsnag\SqlLogger\DoctrineSqlLogger;
use EonX\EasyBugsnag\Tests\Stub\DBAL\Logging\SqlLoggerStub;
use EonX\EasyBugsnag\Tracker\SessionTracker;

final class EasyBugsnagBundleTest extends AbstractSymfonyTestCase
{
    public function testDefaultConfiguratorsOff(): void
    {
        $container = $this->getKernel([__DIR__ . '/../../Fixture/default_configurators_off.yaml'])->getContainer();

        self::assertFalse($container->has(BasicsClientConfigurator::class));
        self::assertFalse($container->has(RuntimeVersionClientConfigurator::class));
    }

    public function testSanityCheck(): void
    {
        $container = $this->getKernel([__DIR__ . '/../../Fixture/default_config.yaml'])->getContainer();

        self::assertInstanceOf(Client::class, $container->get(Client::class));
        self::assertInstanceOf(SessionTracker::class, $container->get(SessionTracker::class));
    }

    public function testSetSqlLoggerOnConfigNoMethodCall(): void
    {
        $container = $this->getKernel([__DIR__ . '/../../Fixture/sql_logger_no_method_call.yaml'])->getContainer();

        self::assertInstanceOf(Configuration::class, $container->get('doctrine.dbal.default_connection.configuration'));
    }

    /**
     * @throws \ReflectionException
     */
    public function testSetSqlLoggerOnConfigWithMethodCall(): void
    {
        $container = $this->getKernel([__DIR__ . '/../../Fixture/sql_logger_with_method_call.yaml'])->getContainer();
        /** @var \Doctrine\DBAL\Configuration $configuration */
        $configuration = $container->get('doctrine.dbal.default_connection.configuration');
        $sqlLogger = $configuration->getSQLLogger();

        self::assertInstanceOf(Configuration::class, $configuration);
        self::assertInstanceOf(DoctrineSqlLogger::class, $sqlLogger);

        if ($sqlLogger instanceof DoctrineSqlLogger) {
            self::assertInstanceOf(SqlLoggerStub::class, $sqlLogger->getDecorated());
        }
    }
}
