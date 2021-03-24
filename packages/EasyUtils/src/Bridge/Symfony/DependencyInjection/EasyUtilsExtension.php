<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Bridge\Symfony\DependencyInjection;

use EonX\EasyUtils\Bridge\BridgeConstantsInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyUtilsExtension extends Extension
{
    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        foreach (BridgeConstantsInterface::MATH_PARAMS as $mathParam) {
            $container->setParameter($mathParam, $config[$mathParam] ?? null);
        }

        $loader = new PhpFileLoader($container, new FileLocator([__DIR__ . '/../Resources/config']));
        $loader->load('services.php');
    }
}
