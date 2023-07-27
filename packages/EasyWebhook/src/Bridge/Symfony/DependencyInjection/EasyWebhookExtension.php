<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony\DependencyInjection;

use EonX\EasyWebhook\Bridge\BridgeConstantsInterface;
use EonX\EasyWebhook\Interfaces\MiddlewareInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Messenger\DependencyInjection\MessengerPass;

final class EasyWebhookExtension extends Extension
{
    private const SIGNATURE_PARAMS = [
        BridgeConstantsInterface::PARAM_SECRET => 'secret',
        BridgeConstantsInterface::PARAM_SIGNATURE_HEADER => 'signature_header',
    ];

    private array $config;

    private ContainerBuilder $container;

    private PhpFileLoader $loader;

    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');
        $loader->load('core_middleware.php');

        $this->config = $config;
        $this->container = $container;
        $this->loader = $loader;

        $this->container->setParameter(BridgeConstantsInterface::PARAM_METHOD, $this->config['method'] ?? null);

        $container
            ->registerForAutoconfiguration(MiddlewareInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_MIDDLEWARE);

        $this->async();
        $this->debug();
        $this->eventHeader();
        $this->idHeader();
        $this->middleware();
        $this->signatureHeader();
    }

    private function async(): void
    {
        $enabled = \class_exists(MessengerPass::class) && ($this->config['async']['enabled'] ?? true);

        $this->container->setParameter(BridgeConstantsInterface::PARAM_ASYNC, $enabled);

        if ($enabled) {
            $this->container->setParameter(BridgeConstantsInterface::PARAM_BUS, $this->config['async']['bus']);
            $this->loader->load('async.php');
        }
    }

    private function debug(): void
    {
        if ($this->container->hasParameter('kernel.debug') && $this->container->getParameter('kernel.debug')) {
            $this->loader->load('debug.php');
        }
    }

    private function eventHeader(): void
    {
        if (($this->config['event']['enabled'] ?? true) === false) {
            return;
        }

        $header = $this->config['event']['event_header'] ?? null;

        $this->container->setParameter(BridgeConstantsInterface::PARAM_EVENT_HEADER, $header);
        $this->loader->load('event.php');
    }

    private function idHeader(): void
    {
        if (($this->config['id']['enabled'] ?? true) === false) {
            return;
        }

        $header = $this->config['id']['id_header'] ?? null;

        $this->container->setParameter(BridgeConstantsInterface::PARAM_ID_HEADER, $header);
        $this->loader->load('id.php');
    }

    private function middleware(): void
    {
        if (($this->config['use_default_middleware'] ?? true) === false) {
            return;
        }

        $this->loader->load('default_middleware.php');
    }

    private function signatureHeader(): void
    {
        if (($this->config['signature']['enabled'] ?? false) === false) {
            return;
        }

        foreach (self::SIGNATURE_PARAMS as $param => $configName) {
            $this->container->setParameter($param, $this->config['signature'][$configName] ?? null);
        }

        $this->container->setAlias(BridgeConstantsInterface::SIGNER, $this->config['signature']['signer']);
        $this->loader->load('signature.php');
    }
}
