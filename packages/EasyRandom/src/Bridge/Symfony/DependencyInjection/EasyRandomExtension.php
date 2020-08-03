<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Bridge\Symfony\DependencyInjection;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PHPFileLoader;
use Symfony\Component\DependencyInjection\Reference;

final class EasyRandomExtension extends Extension
{
    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new PHPFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        if (empty($config['uuid_v4_generator']) === false) {
            $container
                ->getDefinition(RandomGeneratorInterface::class)
                ->addMethodCall('setUuidV4Generator', [new Reference($config['uuid_v4_generator'])]);
        }
    }
}
