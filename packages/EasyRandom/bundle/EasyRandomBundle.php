<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Bundle;

use EonX\EasyRandom\Bundle\Enum\ConfigParam;
use EonX\EasyRandom\Generator\RamseyUuidV4Generator;
use EonX\EasyRandom\Generator\RamseyUuidV6Generator;
use EonX\EasyRandom\Generator\SymfonyUuidV4Generator;
use EonX\EasyRandom\Generator\SymfonyUuidV6Generator;
use EonX\EasyRandom\Generator\UuidGeneratorInterface;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Uid\Uuid as SymfonyUuid;

final class EasyRandomBundle extends AbstractBundle
{
    private const EASY_RANDOM_CONFIG = [
        'uuid_version' => ConfigParam::UuidVersion,
    ];

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        foreach (self::EASY_RANDOM_CONFIG as $name => $param) {
            $container
                ->parameters()
                ->set($param->value, $config[$name]);
        }

        $container->import('config/services.php');

        /** @var int $uuidVersion */
        $uuidVersion = $builder->getParameter(ConfigParam::UuidVersion->value);

        if ($uuidVersion === 4) {
            if (\class_exists(RamseyUuid::class)) {
                $builder->setDefinition(UuidGeneratorInterface::class, new Definition(RamseyUuidV4Generator::class));
            }

            if (\class_exists(SymfonyUuid::class)) {
                $builder->setDefinition(UuidGeneratorInterface::class, new Definition(SymfonyUuidV4Generator::class));
            }
        }

        if ($uuidVersion === 6) {
            if (\class_exists(RamseyUuid::class)) {
                $builder->setDefinition(UuidGeneratorInterface::class, new Definition(RamseyUuidV6Generator::class));
            }

            if (\class_exists(SymfonyUuid::class)) {
                $builder->setDefinition(UuidGeneratorInterface::class, new Definition(SymfonyUuidV6Generator::class));
            }
        }
    }
}
