<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Bundle;

use EonX\EasyNotification\Bundle\Enum\ConfigParam;
use EonX\EasyNotification\Bundle\Enum\ConfigTag;
use EonX\EasyNotification\Configurator\QueueMessageConfiguratorInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyNotificationBundle extends AbstractBundle
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
        $builder
            ->registerForAutoconfiguration(QueueMessageConfiguratorInterface::class)
            ->addTag(ConfigTag::QueueMessageConfigurator->value);

        $container
            ->parameters()
            ->set(ConfigParam::ApiUrl->value, $config['api_url'])
            ->set(ConfigParam::ConfigCacheExpiresAfter->value, $config['config_expires_after']);

        $container->import('config/services.php');
    }
}
