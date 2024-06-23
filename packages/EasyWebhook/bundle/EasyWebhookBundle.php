<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Bundle;

use EonX\EasyWebhook\Bundle\CompilerPass\AddMessengerMiddlewareCompilerPass;
use EonX\EasyWebhook\Bundle\Enum\ConfigParam;
use EonX\EasyWebhook\Bundle\Enum\ConfigServiceId;
use EonX\EasyWebhook\Bundle\Enum\ConfigTag;
use EonX\EasyWebhook\Common\Middleware\MiddlewareInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Messenger\DependencyInjection\MessengerPass;

final class EasyWebhookBundle extends AbstractBundle
{
    private const SIGNATURE_PARAMS = [
        ConfigParam::Secret->value => 'secret',
        ConfigParam::SignatureHeader->value => 'signature_header',
    ];

    private ContainerBuilder $builder;

    private array $config;

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(
            new AddMessengerMiddlewareCompilerPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            -10
        );
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('config/services.php');
        $container->import('config/core_middleware.php');

        $this->config = $config;
        $this->builder = $builder;

        $container
            ->parameters()
            ->set(ConfigParam::Method->value, $this->config['method'] ?? null);

        $builder
            ->registerForAutoconfiguration(MiddlewareInterface::class)
            ->addTag(ConfigTag::Middleware->value);

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
            ->set(ConfigParam::Async->value, $enabled);

        if ($enabled) {
            $container
                ->parameters()
                ->set(ConfigParam::Bus->value, $this->config['async']['bus']);
            $container->import('config/async.php');
        }
    }

    private function debug(ContainerConfigurator $container): void
    {
        if ($this->builder->hasParameter('kernel.debug') && $this->builder->getParameter('kernel.debug')) {
            $container->import('config/debug.php');
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
            ->set(ConfigParam::EventHeader->value, $header);
        $container->import('config/event.php');
    }

    private function idHeader(ContainerConfigurator $container): void
    {
        if (($this->config['id']['enabled'] ?? true) === false) {
            return;
        }

        $header = $this->config['id']['id_header'] ?? null;

        $container
            ->parameters()
            ->set(ConfigParam::IdHeader->value, $header);
        $container->import('config/id.php');
    }

    private function middleware(ContainerConfigurator $container): void
    {
        if (($this->config['use_default_middleware'] ?? true) === false) {
            return;
        }

        $container->import('config/default_middleware.php');
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

        $this->builder->setAlias(ConfigServiceId::Signer->value, $this->config['signature']['signer']);
        $container->import('config/signature.php');
    }
}
