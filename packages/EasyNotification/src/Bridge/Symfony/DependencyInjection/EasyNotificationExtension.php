<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Bridge\Symfony\DependencyInjection;

use EonX\EasyNotification\Bridge\BridgeConstantsInterface;
use EonX\EasyNotification\Interfaces\QueueMessageConfiguratorInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyNotificationExtension extends Extension
{
    /**
     * @var string[]
     */
    private static array $configToParam = [
        'api_url' => BridgeConstantsInterface::PARAM_API_URL,
        'config_expires_after' => BridgeConstantsInterface::PARAM_CONFIG_CACHE_EXPIRES_AFTER,
    ];

    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new PhpFileLoader($container, new FileLocator([__DIR__ . '/../Resources/config']));
        $loader->load('services.php');

        $container
            ->registerForAutoconfiguration(QueueMessageConfiguratorInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_QUEUE_MESSAGE_CONFIGURATOR);

        foreach (self::$configToParam as $configName => $param) {
            $container->setParameter($param, $config[$configName]);
        }
    }
}
