<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyHttpClient\Bridge\BridgeConstantsInterface;
use EonX\EasyHttpClient\Implementations\Symfony\WithEventsHttpClient;
use EonX\EasyWebhook\Bridge\BridgeConstantsInterface as EasyWebhookBridgeConstantsInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class DecorateEasyWebhookClientPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private const DECORATION_SERVICE_ID = 'easy_http_client.decorate_easy_webhook';

    public function process(ContainerBuilder $container): void
    {
        // Apply only if enabled, easy-webhook is install and client definition exists
        if ($this->isEnabled($container) === false
            || \interface_exists(EasyWebhookBridgeConstantsInterface::class) === false
            || $container->has(EasyWebhookBridgeConstantsInterface::HTTP_CLIENT) === false) {
            return;
        }

        $def = (new Definition(WithEventsHttpClient::class))
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setDecoratedService(EasyWebhookBridgeConstantsInterface::HTTP_CLIENT);

        $container->setDefinition(self::DECORATION_SERVICE_ID, $def);
    }

    private function isEnabled(ContainerBuilder $container): bool
    {
        if ($container->hasParameter(BridgeConstantsInterface::PARAM_DECORATE_EASY_WEBHOOK_CLIENT) === false) {
            return false;
        }

        return (bool)$container->getParameter(BridgeConstantsInterface::PARAM_DECORATE_EASY_WEBHOOK_CLIENT);
    }
}
