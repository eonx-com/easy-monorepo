<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Bundle\CompilerPass;

use EonX\EasyServerless\AppMetric\Client\AppMetricClientInterface;
use EonX\EasyServerless\Bundle\Enum\ConfigServiceId;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * The app metric client always references the dedicated logger service, so an application can override it under
 * that id. When nothing provides it (Monolog 3 not installed, no application override), alias it to the application
 * logger. When there is no application logger either, let the client use its default logger, as an ignored invalid
 * reference would be resolved to null.
 */
final class AppMetricLoggerFallbackCompilerPass implements CompilerPassInterface
{
    private const LOGGER_ARGUMENT = '$logger';

    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition(AppMetricClientInterface::class) === false
            || $container->has(ConfigServiceId::AppMetricLogger->value)) {
            return;
        }

        if ($container->has(LoggerInterface::class)) {
            $container->setAlias(ConfigServiceId::AppMetricLogger->value, LoggerInterface::class);

            return;
        }

        $clientDefinition = $container->getDefinition(AppMetricClientInterface::class);
        $arguments = $clientDefinition->getArguments();
        unset($arguments[self::LOGGER_ARGUMENT]);
        $clientDefinition->setArguments($arguments);
    }
}
