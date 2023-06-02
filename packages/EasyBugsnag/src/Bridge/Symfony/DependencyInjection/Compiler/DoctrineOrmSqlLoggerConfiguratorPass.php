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
    private const DBAL_CONFIGURATION_METHOD_CALL = 'setSQLLogger';

    /**
     * @var string
     */
    private const DBAL_CONFIGURATION_PATTERN = 'doctrine.dbal.%s_connection.configuration';

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
            ['default'],
        );

        // Define parent definition for bugsnag sql logger
        $container->setDefinition(
            self::BUGSNAG_ABSTRACT_LOGGER,
            (new Definition(SqlLogger::class, [new Reference(Client::class)]))->setAbstract(true),
        );

        foreach ($connections as $conn) {
            $bugsnagSqlLoggerId = \sprintf(self::BUGSNAG_SQL_LOGGER_PATTERN, $conn);
            $configId = \sprintf(self::DBAL_CONFIGURATION_PATTERN, $conn);
            $connId = \sprintf(self::DBAL_CONNECTION_PATTERN, $conn);

            // Check if dbal configuration for given connection name exists
            if ($container->hasDefinition($configId) === false) {
                throw new InvalidArgumentException(\sprintf(
                    'DBAL configuration for connection "%s" does not exist',
                    $conn,
                ));
            }

            $confDef = $container->getDefinition($configId);

            // Set logger definition for current connection
            $bugsnagSqlLoggerDef = (new ChildDefinition(self::BUGSNAG_ABSTRACT_LOGGER))
                ->setArgument('$conn', new Reference($connId))
                ->setArgument('$connName', $conn);
            $container->setDefinition($bugsnagSqlLoggerId, $bugsnagSqlLoggerDef);

            $sqlLoggerMethodCall = $this->getMethodCall($confDef);

            // Config has method call for sql logger, extend it
            if ($sqlLoggerMethodCall !== null) {
                // Get first argument of method call
                $decorated = $sqlLoggerMethodCall[1][0] ?? null;

                // If first argument is a reference, set it as decorated on our logger definition
                if ($decorated instanceof Reference) {
                    $bugsnagSqlLoggerDef->setArgument('$decorated', $decorated);
                }

                // Remove original method call
                $confDef->removeMethodCall(self::DBAL_CONFIGURATION_METHOD_CALL);
            }

            $confDef->addMethodCall(self::DBAL_CONFIGURATION_METHOD_CALL, [new Reference($bugsnagSqlLoggerId)]);
        }
    }

    /**
     * @return null|mixed[]
     */
    private function getMethodCall(Definition $definition): ?array
    {
        foreach ($definition->getMethodCalls() as $call) {
            if ($call[0] === self::DBAL_CONFIGURATION_METHOD_CALL) {
                return $call;
            }
        }

        return null;
    }

    /**
     * @param null|mixed $default
     *
     * @return null|mixed
     */
    private function getParam(ContainerBuilder $container, string $param, $default = null)
    {
        return $container->hasParameter($param) ? $container->getParameter($param) : $default;
    }
}
