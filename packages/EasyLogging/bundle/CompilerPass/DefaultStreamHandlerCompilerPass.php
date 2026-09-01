<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bundle\CompilerPass;

use EonX\EasyLogging\Bundle\Enum\ConfigParam;
use EonX\EasyLogging\Bundle\Enum\ConfigTag;
use EonX\EasyLogging\Provider\StreamHandlerConfigProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Parameter;

/**
 * @deprecated Will be removed in 7.0, use symfony/monolog-bundle instead.
 */
final class DefaultStreamHandlerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (
            $container->hasParameter(ConfigParam::UseSymfonyMonologBundle->value)
            && (bool)$container->getParameter(ConfigParam::UseSymfonyMonologBundle->value) === true
        ) {
            return;
        }

        // If disabled explicitly, skip
        if ($this->isEnabled($container) === false) {
            return;
        }

        $handlerConfigProviders = $container->findTaggedServiceIds(ConfigTag::HandlerConfigProvider->value);

        // If at least one handler config provider, skip
        if (\count($handlerConfigProviders) > 0) {
            return;
        }

        $def = (new Definition(StreamHandlerConfigProvider::class))
            ->addTag(ConfigTag::HandlerConfigProvider->value)
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->setArgument('$level', new Parameter(ConfigParam::StreamHandlerLevel->value));

        $container->setDefinition(StreamHandlerConfigProvider::class, $def);
    }

    private function isEnabled(ContainerBuilder $container): bool
    {
        if ($container->hasParameter(ConfigParam::StreamHandler->value) === false) {
            return false;
        }

        return (bool)$container->getParameter(ConfigParam::StreamHandler->value);
    }
}
