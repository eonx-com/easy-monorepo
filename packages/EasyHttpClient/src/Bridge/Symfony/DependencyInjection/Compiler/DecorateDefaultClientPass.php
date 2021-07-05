<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyHttpClient\Bridge\BridgeConstantsInterface;
use EonX\EasyHttpClient\Implementations\Symfony\WithEventsHttpClient;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class DecorateDefaultClientPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private const DEFAULT_CLIENT_ID = 'http_client';

    public function process(ContainerBuilder $container): void
    {
        // Apply only if default client definition exists
        if ($container->has(self::DEFAULT_CLIENT_ID) === false) {
            return;
        }

        // Apply only if configured to do so
        if ($this->isEnabled($container) === false) {
            return;
        }

        $def = (new Definition(WithEventsHttpClient::class))
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setDecoratedService(self::DEFAULT_CLIENT_ID);

        $container->setDefinition(WithEventsHttpClient::class, $def);
    }

    private function isEnabled(ContainerBuilder $container): bool
    {
        if ($container->hasParameter(BridgeConstantsInterface::PARAM_DECORATE_DEFAULT_CLIENT) === false) {
            return false;
        }

        return (bool)$container->getParameter(BridgeConstantsInterface::PARAM_DECORATE_DEFAULT_CLIENT);
    }
}
