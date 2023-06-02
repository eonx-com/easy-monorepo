<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyLogging\Bridge\BridgeConstantsInterface;
use EonX\EasyLogging\Config\StreamHandlerConfigProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class DefaultStreamHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // If disabled explicitly, skip
        if ($this->isEnabled($container) === false) {
            return;
        }

        $handlerConfigProviders = $container->findTaggedServiceIds(
            BridgeConstantsInterface::TAG_HANDLER_CONFIG_PROVIDER
        );

        // If at least one handler config provider, skip
        if (\count($handlerConfigProviders) > 0) {
            return;
        }

        $def = (new Definition(StreamHandlerConfigProvider::class))
            ->addTag(BridgeConstantsInterface::TAG_HANDLER_CONFIG_PROVIDER)
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->setArgument('$level', '%' . BridgeConstantsInterface::PARAM_STREAM_HANDLER_LEVEL . '%');

        $container->setDefinition(StreamHandlerConfigProvider::class, $def);
    }

    private function isEnabled(ContainerBuilder $container): bool
    {
        if ($container->hasParameter(BridgeConstantsInterface::PARAM_STREAM_HANDLER) === false) {
            return false;
        }

        return (bool)$container->getParameter(BridgeConstantsInterface::PARAM_STREAM_HANDLER);
    }
}
