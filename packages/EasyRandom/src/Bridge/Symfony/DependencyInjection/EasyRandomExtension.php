<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Bridge\Symfony\DependencyInjection;

use EonX\EasyRandom\Bridge\BridgeConstantsInterface;
use EonX\EasyRandom\Enums\UuidVersion;
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
     * @param array<string, mixed> $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new PhpFileLoader($container, new FileLocator([__DIR__ . '/../Resources/config']));

        $container->setParameter(
            BridgeConstantsInterface::PARAM_DEFAULT_UUID_VERSION,
            UuidVersion::from($config['default_uuid_version'])
        );

        $loader->load('services.php');

        $container->getDefinition(RandomGeneratorInterface::class)
            ->setArgument('$uuidV4Generator', $this->resolveUuidV4GeneratorService($config))
            ->setArgument('$uuidV6Generator', $this->resolveUuidV6GeneratorService($config));
    }

    /**
     * @param array<string, mixed> $config
     */
    private function resolveUuidV4GeneratorService(array $config): ?Reference
    {
        /** @var string|null $uuidV4Generator */
        $uuidV4Generator = $config['uuid_v4_generator'] ?? null;

        if ($uuidV4Generator === null && \class_exists(RamseyUuid::class)) {
            $uuidV4Generator = BridgeConstantsInterface::SERVICE_RAMSEY_UUID4;
        }

        if ($uuidV4Generator === null && \class_exists(SymfonyUuid::class)) {
            $uuidV4Generator = BridgeConstantsInterface::SERVICE_SYMFONY_UUID4;
        }

        return $uuidV4Generator === null ? null : new Reference($uuidV4Generator);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function resolveUuidV6GeneratorService(array $config): ?Reference
    {
        /** @var string|null $uuidV6Generator */
        $uuidV6Generator = $config['uuid_v6_generator'] ?? null;

        if ($uuidV6Generator === null && \class_exists(RamseyUuid::class)) {
            $uuidV6Generator = BridgeConstantsInterface::SERVICE_RAMSEY_UUID6;
        }

        if ($uuidV6Generator === null && \class_exists(SymfonyUuid::class)) {
            $uuidV6Generator = BridgeConstantsInterface::SERVICE_SYMFONY_UUID6;
        }

        return $uuidV6Generator === null ? null : new Reference($uuidV6Generator);
    }
}
