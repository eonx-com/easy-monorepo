<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Bridge\Symfony\DependencyInjection;

use EonX\EasyRandom\Generators\RamseyUuidV4Generator;
use EonX\EasyRandom\Generators\SymfonyUidUuidV4Generator;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Uid\Uuid as SymfonyUuid;

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

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $uuidV4Generator = $config['uuid_v4_generator'] ?? null;

        if ($uuidV4Generator === null && \class_exists(RamseyUuid::class)) {
            $uuidV4Generator = RamseyUuidV4Generator::class;
        }

        if ($uuidV4Generator === null && \class_exists(SymfonyUuid::class)) {
            $uuidV4Generator = SymfonyUidUuidV4Generator::class;
        }

        if ($uuidV4Generator !== null) {
            $container
                ->getDefinition(RandomGeneratorInterface::class)
                ->addMethodCall('setUuidV4Generator', [new Reference($uuidV4Generator)]);
        }
    }
}
