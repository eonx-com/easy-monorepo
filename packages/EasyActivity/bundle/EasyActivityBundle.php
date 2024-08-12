<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Bundle;

use EonX\EasyActivity\Bundle\Enum\ConfigParam;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyActivityBundle extends AbstractBundle
{
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
        $container
            ->parameters()
            ->set(ConfigParam::TableName->value, $config['table_name'])
            ->set(ConfigParam::DisallowedProperties->value, $config['disallowed_properties'])
            ->set(ConfigParam::Subjects->value, $config['subjects']);

        $container->import('config/services.php');

        $this->registerEasyDoctrineConfiguration($config, $container, $builder);
    }

    private function registerEasyDoctrineConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        if ($builder->hasExtension('easy_doctrine') === false) {
            return;
        }

        $container
            ->parameters()
            ->set(ConfigParam::EasyDoctrineSubscriberEnabled->value, $config['easy_doctrine']['subscriber']['enabled']);

        $container->import('config/easy_doctrine.php');

        $builder->prependExtensionConfig(
            'easy_doctrine',
            [
                'deferred_dispatcher_entities' => \array_keys($config['subjects']),
            ]
        );
    }
}
