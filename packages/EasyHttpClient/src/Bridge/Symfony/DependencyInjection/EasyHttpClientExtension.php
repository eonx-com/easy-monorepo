<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Bridge\Symfony\DependencyInjection;

use Bugsnag\Client;
use EonX\EasyHttpClient\Bridge\BridgeConstantsInterface;
use EonX\EasyHttpClient\Interfaces\RequestDataModifierInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyHttpClientExtension extends Extension
{
    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new PhpFileLoader($container, new FileLocator([__DIR__ . '/../Resources/config']));

        $container
            ->registerForAutoconfiguration(RequestDataModifierInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_REQUEST_DATA_MODIFIER);

        $container->setParameter(
            BridgeConstantsInterface::PARAM_DECORATE_DEFAULT_CLIENT,
            $config['decorate_default_client'] ?? false
        );

        $container->setParameter(
            BridgeConstantsInterface::PARAM_DECORATE_EASY_WEBHOOK_CLIENT,
            $config['decorate_easy_webhook_client'] ?? false
        );

        $container->setParameter(
            BridgeConstantsInterface::PARAM_MODIFIERS_ENABLED,
            $config['modifiers']['enabled'] ?? true
        );

        $modifiersWhitelist = $config['modifiers']['whitelist'] ?? [null];
        $container->setParameter(
            BridgeConstantsInterface::PARAM_MODIFIERS_WHITELIST,
            \count($modifiersWhitelist) === 1 && ($modifiersWhitelist[0] === null) ? null : $modifiersWhitelist
        );

        $loader->load('http_client.php');

        if (($config['easy_bugsnag_enabled'] ?? true) && \class_exists(Client::class)) {
            $loader->load('easy_bugsnag.php');
        }

        if (($config['psr_logger_enabled'] ?? true) && \interface_exists(LoggerInterface::class)) {
            $loader->load('psr_logger.php');
        }
    }
}
