<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bundle\CompilerPass;

use EonX\EasyLogging\Bundle\Enum\ConfigParam;
use EonX\EasyLogging\Factory\LoggerFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @deprecated Will be removed in 7.0, use symfony/monolog-bundle instead.
 */
final class ReplaceChannelsDefinitionCompilerPass implements CompilerPassInterface
{
    private const MONOLOG_LOGGER_ID_PATTERN = 'monolog.logger.%s';

    private const MONOLOG_LOGGER_TAG = 'monolog.logger';

    public function process(ContainerBuilder $container): void
    {
        if (
            $container->hasParameter(ConfigParam::UseSymfonyMonologBundle->value)
            && (bool)$container->getParameter(ConfigParam::UseSymfonyMonologBundle->value) === true
        ) {
            return;
        }

        $container->addAliases([
            'logger' => 'easy_logging.logger',
            LoggerInterface::class => 'logger',
        ]);

        $defaultChannel = $container->getParameter(ConfigParam::DefaultChannel->value);

        foreach ($container->findTaggedServiceIds(self::MONOLOG_LOGGER_TAG) as $tags) {
            foreach ($tags as $tag) {
                if ($defaultChannel === ($tag['channel'] ?? $defaultChannel)) {
                    continue;
                }

                /** @var string $resolvedChannel */
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
