<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bridge\Symfony\DependencyInjection;

use EonX\EasyApiPlatform\Bridge\BridgeConstantsInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyApiPlatformExtension extends Extension
{
    /**
     * @var array<string, string>
     */
    private const EASY_API_PLATFORM_CONFIG = [
        'custom_paginator_enabled' => BridgeConstantsInterface::PARAM_CUSTOM_PAGINATOR_ENABLED,
    ];

    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        foreach (self::EASY_API_PLATFORM_CONFIG as $name => $param) {
            $container->setParameter($param, $config[$name]);
        }

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');
        $loader->load('filters.php');

        if ($config['custom_paginator_enabled'] ?? true) {
            $loader->load('pagination.php');
        }
    }
}
