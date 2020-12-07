<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony\DependencyInjection;

use EonX\EasyWebhook\Bridge\BridgeConstantsInterface;
use EonX\EasyWebhook\Interfaces\WebhookConfiguratorInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Messenger\DependencyInjection\MessengerPass;

final class EasyWebhookExtension extends Extension
{
    /**
     * @var string[]
     */
    private static $signatureParams = [
        BridgeConstantsInterface::PARAM_SIGNATURE_HEADER => 'signature_header',
        BridgeConstantsInterface::PARAM_SECRET => 'secret',
    ];

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

        $container
            ->registerForAutoconfiguration(WebhookConfiguratorInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_WEBHOOK_CONFIGURATOR);

        if ($config['use_default_configurators'] ?? true) {
            $container->setParameter(BridgeConstantsInterface::PARAM_METHOD, $config['method'] ?? null);

            $loader->load('default_configurators.php');
        }

        if ($config['event']['enabled'] ?? true) {
            $container->setParameter(BridgeConstantsInterface::PARAM_EVENT_HEADER, $config['event']['event_header']);

            $loader->load('event.php');
        }

        if ($config['id']['enabled'] ?? true) {
            $container->setParameter(BridgeConstantsInterface::PARAM_ID_HEADER, $config['id']['id_header'] ?? null);

            $loader->load('id.php');
        }

        if ($config['signature']['enabled'] ?? false) {
            foreach (static::$signatureParams as $param => $configName) {
                $container->setParameter($param, $config['signature'][$configName] ?? null);
            }

            $container->setAlias(BridgeConstantsInterface::SIGNER, $config['signature']['signer']);

            $loader->load('signature.php');
        }

        if (\class_exists(MessengerPass::class) && ($config['async']['enabled'] ?? true)) {
            $container->setParameter(BridgeConstantsInterface::PARAM_BUS, $config['async']['bus']);

            $loader->load('messenger_client.php');
        }

        if ($container->hasParameter('kernel.debug') && $container->getParameter('kernel.debug')) {
            $loader->load('debug.php');
        }
    }
}
