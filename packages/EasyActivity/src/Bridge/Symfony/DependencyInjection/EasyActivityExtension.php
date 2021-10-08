<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Symfony\DependencyInjection;

use EonX\EasyActivity\Bridge\Symfony\BridgeConstantsInterface;
use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyActivityExtension extends Extension
{
    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter(
            BridgeConstantsInterface::PARAM_DISALLOWED_PROPERTIES,
            $config['disallowed_properties']
        );
        $container->setParameter(BridgeConstantsInterface::PARAM_SUBJECTS, $config['subjects']);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        /** @var array<string, string> $bundles */
        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['EasyDoctrineSymfonyBundle']) === false) {
            throw new RuntimeException('EasyDoctrineSymfonyBundle is not registered');
        }
    }
}
