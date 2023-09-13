<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Bridge\Symfony;

use EonX\EasyRandom\Bridge\BridgeConstantsInterface;
use EonX\EasyRandom\Bridge\Ramsey\Generators\RamseyUuidV4Generator;
use EonX\EasyRandom\Bridge\Ramsey\Generators\RamseyUuidV6Generator;
use EonX\EasyRandom\Bridge\Symfony\Generators\SymfonyUuidV4Generator;
use EonX\EasyRandom\Bridge\Symfony\Generators\SymfonyUuidV6Generator;
use EonX\EasyRandom\Interfaces\UuidGeneratorInterface;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Uid\Uuid as SymfonyUuid;

final class EasyRandomSymfonyBundle extends AbstractBundle
{
    private const EASY_RANDOM_CONFIG = [
        'uuid_version' => BridgeConstantsInterface::PARAM_UUID_VERSION,
    ];

    protected string $extensionAlias = 'easy_random';

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__ . '/Resources/config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        foreach (self::EASY_RANDOM_CONFIG as $name => $param) {
            $container
                ->parameters()
                ->set($param, $config[$name]);
        }

        $container->import(__DIR__ . '/Resources/config/services.php');

        /** @var int $uuidVersion */
        $uuidVersion = $builder->getParameter(BridgeConstantsInterface::PARAM_UUID_VERSION);

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
