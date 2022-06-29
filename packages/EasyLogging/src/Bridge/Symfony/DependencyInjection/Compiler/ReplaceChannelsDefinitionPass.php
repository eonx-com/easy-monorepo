<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyLogging\Bridge\BridgeConstantsInterface;
use EonX\EasyLogging\Interfaces\LoggerFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class ReplaceChannelsDefinitionPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private const MONOLOG_LOGGER_ID_PATTERN = 'monolog.logger.%s';

    /**
     * @var string
     */
    private const MONOLOG_LOGGER_TAG = 'monolog.logger';

    public function process(ContainerBuilder $container): void
    {
        $container->addAliases([
            'logger' => 'easy_logging.logger',
            LoggerInterface::class => 'logger',
        ]);

        $defaultChannel = $container->getParameter(BridgeConstantsInterface::PARAM_DEFAULT_CHANNEL);

        foreach ($container->findTaggedServiceIds(self::MONOLOG_LOGGER_TAG) as $tags) {
            foreach ($tags as $tag) {
                if ($defaultChannel === ($tag['channel'] ?? $defaultChannel)) {
                    continue;
                }

                $resolvedChannel = $container->getParameterBag()
                    ->resolveValue($tag['channel']);
                $loggerId = \sprintf(self::MONOLOG_LOGGER_ID_PATTERN, $resolvedChannel);

                if ($container->hasDefinition($loggerId) === false) {
                    continue;
                }

                $newDefinition = (new Definition(LoggerInterface::class))
                    ->setFactory([new Reference(LoggerFactoryInterface::class), 'create'])
                    ->setArguments([$resolvedChannel]);

                $container->setDefinition($loggerId, $newDefinition);
            }
        }
    }
}
