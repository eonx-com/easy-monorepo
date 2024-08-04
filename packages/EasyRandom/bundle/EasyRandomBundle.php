<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Bundle;

use EonX\EasyRandom\Bundle\Enum\ConfigParam;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

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
    }
}
