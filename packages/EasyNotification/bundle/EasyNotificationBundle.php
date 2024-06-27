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
    private static array $configToParam = [
        'api_url' => ConfigParam::ApiUrl,
        'config_expires_after' => ConfigParam::ConfigCacheExpiresAfter,
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
        $container->import('config/services.php');

        $builder
            ->registerForAutoconfiguration(QueueMessageConfiguratorInterface::class)
            ->addTag(ConfigTag::QueueMessageConfigurator->value);

        foreach (self::$configToParam as $configName => $param) {
            $container
                ->parameters()
                ->set($param->value, $config[$configName]);
        }
    }
}
