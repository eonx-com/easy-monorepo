<?php

declare(strict_types=1);

namespace EonX\EasyLock\Bridge\Symfony\DependencyInjection;

use EonX\EasyLock\Bridge\BridgeConstantsInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Messenger\DependencyInjection\MessengerPass;

final class EasyLockExtension extends Extension
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

        $container->setParameter(BridgeConstantsInterface::PARAM_CONNECTION, $config['connection']);

        if (\class_exists(MessengerPass::class)) {
            $container->setParameter(
                BridgeConstantsInterface::PARAM_MESSENGER_MIDDLEWARE_AUTO_REGISTER,
                $config['messenger_middleware_auto_register'] ?? true
            );

            $loader->load('messenger_middleware.php');
        }
    }
}
