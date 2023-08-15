<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony;

use EonX\EasyWebhook\Bridge\BridgeConstantsInterface;
use EonX\EasyWebhook\Bridge\Symfony\DependencyInjection\Compiler\AddMessengerMiddlewarePass;
use EonX\EasyWebhook\Interfaces\MiddlewareInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Messenger\DependencyInjection\MessengerPass;

final class EasyWebhookSymfonyBundle extends AbstractBundle
{
    private const SIGNATURE_PARAMS = [
        BridgeConstantsInterface::PARAM_SECRET => 'secret',
        BridgeConstantsInterface::PARAM_SIGNATURE_HEADER => 'signature_header',
    ];

    protected string $extensionAlias = 'easy_webhook';

    private ContainerBuilder $builder;

    private array $config;

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new AddMessengerMiddlewarePass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -10);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__ . '/Resources/config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import(__DIR__ . '/Resources/config/services.php');
        $container->import(__DIR__ . '/Resources/config/core_middleware.php');

        $this->config = $config;
        $this->builder = $builder;

        $container
            ->parameters()
            ->set(BridgeConstantsInterface::PARAM_METHOD, $this->config['method'] ?? null);

        $builder
            ->registerForAutoconfiguration(MiddlewareInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_MIDDLEWARE);

        $this->async($container);
        $this->debug($container);
        $this->eventHeader($container);
        $this->idHeader($container);
        $this->middleware($container);
        $this->signatureHeader($container);
    }

    private function async(ContainerConfigurator $container): void
    {
        $enabled = \class_exists(MessengerPass::class) && ($this->config['async']['enabled'] ?? true);

        $container
            ->parameters()
            ->set(BridgeConstantsInterface::PARAM_ASYNC, $enabled);

        if ($enabled) {
            $container
                ->parameters()
                ->set(BridgeConstantsInterface::PARAM_BUS, $this->config['async']['bus']);
            $container->import(__DIR__ . '/Resources/config/async.php');
        }
    }

    private function debug(ContainerConfigurator $container): void
    {
        if ($this->builder->hasParameter('kernel.debug') && $this->builder->getParameter('kernel.debug')) {
            $container->import(__DIR__ . '/Resources/config/debug.php');
        }
    }

    private function eventHeader(ContainerConfigurator $container): void
    {
        if (($this->config['event']['enabled'] ?? true) === false) {
            return;
        }

        $header = $this->config['event']['event_header'] ?? null;

        $container
            ->parameters()
            ->set(BridgeConstantsInterface::PARAM_EVENT_HEADER, $header);
        $container->import(__DIR__ . '/Resources/config/event.php');
    }

    private function idHeader(ContainerConfigurator $container): void
    {
        if (($this->config['id']['enabled'] ?? true) === false) {
            return;
        }

        $header = $this->config['id']['id_header'] ?? null;

        $container
            ->parameters()
            ->set(BridgeConstantsInterface::PARAM_ID_HEADER, $header);
        $container->import(__DIR__ . '/Resources/config/id.php');
    }

    private function middleware(ContainerConfigurator $container): void
    {
        if (($this->config['use_default_middleware'] ?? true) === false) {
            return;
        }

        $container->import(__DIR__ . '/Resources/config/default_middleware.php');
    }

    private function signatureHeader(ContainerConfigurator $container): void
    {
        if (($this->config['signature']['enabled'] ?? false) === false) {
            return;
        }

        foreach (self::SIGNATURE_PARAMS as $param => $configName) {
            $container
                ->parameters()
                ->set($param, $this->config['signature'][$configName] ?? null);
        }

        $this->builder->setAlias(BridgeConstantsInterface::SIGNER, $this->config['signature']['signer']);
        $container->import(__DIR__ . '/Resources/config/signature.php');
    }
}
