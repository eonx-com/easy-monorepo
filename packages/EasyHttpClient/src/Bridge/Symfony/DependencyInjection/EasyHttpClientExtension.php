<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Bridge\Symfony\DependencyInjection;

use Bugsnag\Client;
use EonX\EasyHttpClient\Bridge\BridgeConstantsInterface;
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

        $container->setParameter(
            BridgeConstantsInterface::PARAM_DECORATE_DEFAULT_CLIENT,
            $config['decorate_default_client'] ?? false
        );

        if (($config['easy_bugsnag_enabled'] ?? true) && \class_exists(Client::class)) {
            $loader->load('easy_bugsnag.php');
        }

        if (($config['psr_logger_enabled'] ?? true) && \interface_exists(LoggerInterface::class)) {
            $loader->load('psr_logger.php');
        }
    }
}
