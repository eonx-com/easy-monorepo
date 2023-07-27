<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\DependencyInjection;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyAsync\Bridge\BridgeConstantsInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Messenger\DependencyInjection\MessengerPass;

final class EasyAsyncExtension extends Extension
{
    private array $config;

    private ContainerBuilder $container;

    private PhpFileLoader $loader;

    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->config = $this->processConfiguration(new Configuration(), $configs);
        $this->container = $container;
        $this->loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $this->messenger();
    }

    /**
     * @throws \Exception
     */
    private function messenger(): void
    {
        if (\class_exists(MessengerPass::class) === false) {
            return;
        }

        // Messenger Middleware auto register
        $this->container->setParameter(
            BridgeConstantsInterface::PARAM_MESSENGER_MIDDLEWARE_AUTO_REGISTER,
            $this->config['messenger_middleware_auto_register'] ?? true
        );

        $this->loader->load('messenger.php');

        if (\interface_exists(EntityManagerInterface::class)) {
            $this->loader->load('messenger_doctrine.php');
        }

        // Stop Worker On Messages
        if ($this->config['messenger_worker']['stop_on_messages_limit']['enabled'] ?? false) {
            $this->container->setParameter(
                BridgeConstantsInterface::PARAM_MESSENGER_WORKER_STOP_MIN_MESSAGES,
                $this->config['messenger_worker']['stop_on_messages_limit']['min_messages']
            );

            $this->container->setParameter(
                BridgeConstantsInterface::PARAM_MESSENGER_WORKER_STOP_MAX_MESSAGES,
                $this->config['messenger_worker']['stop_on_messages_limit']['max_messages']
            );

            $this->loader->load('messenger_stop_on_messages_limit.php');
        }

        // Stop Worker On Time
        if ($this->config['messenger_worker']['stop_on_time_limit']['enabled'] ?? false) {
            $this->container->setParameter(
                BridgeConstantsInterface::PARAM_MESSENGER_WORKER_STOP_MIN_TIME,
                $this->config['messenger_worker']['stop_on_time_limit']['min_time']
            );

            $this->container->setParameter(
                BridgeConstantsInterface::PARAM_MESSENGER_WORKER_STOP_MAX_TIME,
                $this->config['messenger_worker']['stop_on_time_limit']['max_time']
            );

            $this->loader->load('messenger_stop_on_time_limit.php');
        }
    }
}
