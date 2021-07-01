<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Symfony\DependencyInjection;

use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface;
use EonX\EasyBugsnag\Interfaces\ClientConfiguratorInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyBugsnagExtension extends Extension
{
    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $container->setParameter(BridgeConstantsInterface::PARAM_API_KEY, $config['api_key']);
        $container->setParameter(
            BridgeConstantsInterface::PARAM_DOCTRINE_DBAL_ENABLED,
            $config['doctrine_dbal']['enabled'] ?? false
        );
        $container->setParameter(
            BridgeConstantsInterface::PARAM_DOCTRINE_DBAL_CONNECTIONS,
            $config['doctrine_dbal']['connections'] ?? 'default'
        );

        $container
            ->registerForAutoconfiguration(ClientConfiguratorInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_CLIENT_CONFIGURATOR);

        if ($config['session_tracking'] ?? false) {
            $container->setParameter(
                BridgeConstantsInterface::PARAM_SESSION_TRACKING_EXCLUDE,
                $config['session_tracking_exclude'] ?? []
            );

            $container->setParameter(
                BridgeConstantsInterface::PARAM_SESSION_TRACKING_EXCLUDE_DELIMITER,
                $config['session_tracking_exclude_delimiter'] ?? '#'
            );

            $loader->load('sessions.php');
        }

        if ($config['worker_info'] ?? false) {
            $loader->load('worker.php');
        }
    }
}
