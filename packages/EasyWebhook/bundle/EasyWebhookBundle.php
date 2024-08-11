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

final class EasyWebhookBundle extends AbstractBundle
{
    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new AddMessengerMiddlewareCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -10);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder
            ->registerForAutoconfiguration(MiddlewareInterface::class)
            ->addTag(ConfigTag::Middleware->value);

        $container
            ->parameters()
            ->set(ConfigParam::Method->value, $config['method']);

        $container->import('config/services.php');
        $container->import('config/core_middleware.php');

        if ($builder->hasParameter('kernel.debug') && $builder->getParameter('kernel.debug')) {
            $container->import('config/debug.php');
        }

        if ($config['use_default_middleware']) {
            $container->import('config/default_middleware.php');
        }

        $this->registerAsyncConfiguration($config, $container, $builder);
        $this->registerEventHeaderConfiguration($config, $container, $builder);
        $this->registerIdHeaderConfiguration($config, $container, $builder);
        $this->registerSignatureHeaderConfiguration($config, $container, $builder);
    }

    private function registerAsyncConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        if ($config['async']['enabled'] === false) {
            return;
        }

        $container
            ->parameters()
            ->set(ConfigParam::AsyncEnabled->value, $config['async']['enabled'])
            ->set(ConfigParam::Bus->value, $config['async']['bus']);

        $container->import('config/async.php');
    }

    private function registerEventHeaderConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        if ($config['event']['enabled'] === false) {
            return;
        }

        $container
            ->parameters()
            ->set(ConfigParam::EventHeader->value, $config['event']['header']);

        $container->import('config/event.php');
    }

    private function registerIdHeaderConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        if ($config['id']['enabled'] === false) {
            return;
        }

        $container
            ->parameters()
            ->set(ConfigParam::IdHeader->value, $config['id']['header']);

        $container->import('config/id_header.php');
    }

    private function registerSignatureHeaderConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        if ($config['signature']['enabled'] === false) {
            return;
        }

        $container
            ->parameters()
            ->set(ConfigParam::Secret->value, $config['signature']['secret'])
            ->set(ConfigParam::SignatureHeader->value, $config['signature']['header']);

        $builder->setAlias(ConfigServiceId::Signer->value, $config['signature']['signer']);

        $container->import('config/signature.php');
    }
}
