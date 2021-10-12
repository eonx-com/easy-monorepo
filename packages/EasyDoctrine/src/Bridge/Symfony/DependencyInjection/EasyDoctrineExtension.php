<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony\DependencyInjection;

use EonX\EasyDoctrine\Bridge\BridgeConstantsInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyDoctrineExtension extends Extension
{
    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter(BridgeConstantsInterface::PARAM_ENTITIES, $config['entities']);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        /** @var array<string, string> $bundles */
        $bundles = $container->getParameter('kernel.bundles');

        if ($config['easy_error_handler_enabled'] && isset($bundles['EasyErrorHandlerSymfonyBundle']) === true) {
            $loader->load('easy-error-handler-listener.php');
        }
    }
}
