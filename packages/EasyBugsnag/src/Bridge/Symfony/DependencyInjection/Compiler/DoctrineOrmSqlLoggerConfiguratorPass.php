<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Symfony\DependencyInjection\Compiler;

use Bugsnag\Client;
use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface;
use EonX\EasyBugsnag\Bridge\Doctrine\DBAL\SqlLogger;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class DoctrineOrmSqlLoggerConfiguratorPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private const BUGSNAG_ABSTRACT_LOGGER = 'easy_bugsnag.sql_logger.abstract';

    /**
     * @var string
     */
    private const BUGSNAG_SQL_LOGGER_PATTERN = 'easy_bugsnag.sql_logger.%s';

    /**
     * @var string
     */
    private const DBAL_CHAIN_LOGGER_PATTERN = 'doctrine.dbal.logger.chain.%s';

    /**
     * @var string
     */
    private const DBAL_CONNECTION_PATTERN = 'doctrine.dbal.%s_connection';

    public function process(ContainerBuilder $container): void
    {
        $enabled = $this->getParam($container, BridgeConstantsInterface::PARAM_DOCTRINE_DBAL_ENABLED, false);

        if ($enabled === false) {
            return;
        }

        $connections = $this->getParam(
            $container,
            BridgeConstantsInterface::PARAM_DOCTRINE_DBAL_CONNECTIONS,
            ['default']
        );

        // Define parent definition for bugsnag sql logger
        $container->setDefinition(
            self::BUGSNAG_ABSTRACT_LOGGER,
            (new Definition(SqlLogger::class, [new Reference(Client::class)]))->setAbstract(true)
        );

        foreach ($connections as $conn) {
            $bugsnagSqlLoggerId = \sprintf(self::BUGSNAG_SQL_LOGGER_PATTERN, $conn);
            $chainLoggerId = \sprintf(self::DBAL_CHAIN_LOGGER_PATTERN, $conn);
            $connId = \sprintf(self::DBAL_CONNECTION_PATTERN, $conn);

            // Check if chain logger for given connection name exists
            if ($container->hasDefinition($chainLoggerId) === false) {
                throw $this->invalidDbalConn($conn, 'logger.chain');
            }

            // Check if dbal connection for given connection name exists
            if ($container->hasDefinition($connId) === false) {
                throw $this->invalidDbalConn($conn, 'connection');
            }

            $chainLoggerDef = $container->getDefinition($chainLoggerId);

            $container->setDefinition(
                $bugsnagSqlLoggerId,
                (new ChildDefinition(self::BUGSNAG_ABSTRACT_LOGGER))->setArgument('$conn', new Reference($connId))
            );

            $chainLoggerDef->addMethodCall('addLogger', [new Reference($bugsnagSqlLoggerId)]);
        }
    }

    private function getParam(ContainerBuilder $container, string $param, $default = null)
    {
        return $container->hasParameter($param) ? $container->getParameter($param) : $default;
    }

    private function invalidDbalConn(string $conn, string $type): InvalidArgumentException
    {
        return new InvalidArgumentException(\sprintf('DBAL %s for connection "%s" does not exist', $type, $conn));
    }
}
