<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Bundle;

use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface as EasyErrorHandlerBridgeConstantsInterface;
use EonX\EasyHttpClient\Bridge\BridgeConstantsInterface as EasyHttpClientBridgeConstantsInterface;
use EonX\EasyLogging\Bridge\BridgeConstantsInterface as EasyLoggingBridgeConstantsInterface;
use EonX\EasyRequestId\Bundle\Enum\ConfigParam;
use EonX\EasyWebhook\Bridge\BridgeConstantsInterface as EasyWebhookBridgeConstantsInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyRequestIdBundle extends AbstractBundle
{
    private array $config;

    private ContainerConfigurator $containerConfigurator;

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // HTTP headers
        $container
            ->parameters()
            ->set(
                ConfigParam::HttpHeaderCorrelationId->value,
                $config['http_headers']['correlation_id']
            );

        $container
            ->parameters()
            ->set(
                ConfigParam::HttpHeaderRequestId->value,
                $config['http_headers']['request_id']
            );

        $container->import('config/services.php');

        $this->config = $config;
        $this->containerConfigurator = $container;

        $this->loadIfEnabled('easy_error_handler', EasyErrorHandlerBridgeConstantsInterface::class);
        $this->loadIfEnabled('easy_logging', EasyLoggingBridgeConstantsInterface::class);
        $this->loadIfEnabled('easy_http_client', EasyHttpClientBridgeConstantsInterface::class);
        $this->loadIfEnabled('easy_webhook', EasyWebhookBridgeConstantsInterface::class);
    }

    private function loadIfEnabled(string $configName, ?string $interface = null): void
    {
        // Load only if interface exists
        if ($interface !== null && \interface_exists($interface) === false) {
            return;
        }

        // Load only if enabled in config
        if (($this->config[$configName] ?? true) === false) {
            return;
        }

        $this->containerConfigurator->import("config/$configName.php");
    }
}
