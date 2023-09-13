<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Bridge\Symfony;

use EonX\EasyNotification\Bridge\BridgeConstantsInterface;
use EonX\EasyNotification\Interfaces\QueueMessageConfiguratorInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyNotificationSymfonyBundle extends AbstractBundle
{
    protected string $extensionAlias = 'easy_notification';

    private static array $configToParam = [
        'api_url' => BridgeConstantsInterface::PARAM_API_URL,
        'config_expires_after' => BridgeConstantsInterface::PARAM_CONFIG_CACHE_EXPIRES_AFTER,
    ];

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
        $container->import(__DIR__ . '/Resources/config/services.php');

        $builder
            ->registerForAutoconfiguration(QueueMessageConfiguratorInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_QUEUE_MESSAGE_CONFIGURATOR);

        foreach (self::$configToParam as $configName => $param) {
            $container
                ->parameters()
                ->set($param, $config[$configName]);
        }
    }
}
