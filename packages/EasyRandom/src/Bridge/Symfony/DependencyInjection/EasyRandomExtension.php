<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Bridge\Symfony\DependencyInjection;

use EonX\EasyRandom\Bridge\BridgeConstantsInterface;
use EonX\EasyRandom\Bridge\Ramsey\Generators\RamseyUuidV4Generator;
use EonX\EasyRandom\Bridge\Ramsey\Generators\RamseyUuidV6Generator;
use EonX\EasyRandom\Bridge\Symfony\Generators\SymfonyUuidV4Generator;
use EonX\EasyRandom\Bridge\Symfony\Generators\SymfonyUuidV6Generator;
use EonX\EasyRandom\Interfaces\UuidGeneratorInterface;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Uid\Uuid as SymfonyUuid;

final class EasyRandomExtension extends Extension
{
    /**
     * @var array<string, string>
     */
    private const EASY_RANDOM_CONFIG = [
        'uuid_version' => BridgeConstantsInterface::PARAM_UUID_VERSION,
    ];

    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        foreach (self::EASY_RANDOM_CONFIG as $name => $param) {
            $container->setParameter($param, $config[$name]);
        }

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        /** @var int $uuidVersion */
        $uuidVersion = $container->getParameter(BridgeConstantsInterface::PARAM_UUID_VERSION);

        if ($uuidVersion === 4) {
            if (\class_exists(RamseyUuid::class)) {
                $container->setDefinition(UuidGeneratorInterface::class, new Definition(RamseyUuidV4Generator::class));
            }

            if (\class_exists(SymfonyUuid::class)) {
                $container->setDefinition(UuidGeneratorInterface::class, new Definition(SymfonyUuidV4Generator::class));
            }
        }

        if ($uuidVersion === 6) {
            if (\class_exists(RamseyUuid::class)) {
                $container->setDefinition(UuidGeneratorInterface::class, new Definition(RamseyUuidV6Generator::class));
            }

            if (\class_exists(SymfonyUuid::class)) {
                $container->setDefinition(UuidGeneratorInterface::class, new Definition(SymfonyUuidV6Generator::class));
            }
        }
    }
}
