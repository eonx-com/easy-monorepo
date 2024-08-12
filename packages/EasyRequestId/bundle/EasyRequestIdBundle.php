<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Bundle;

use EonX\EasyErrorHandler\Bundle\Enum\ConfigTag as EasyErrorHandlerConfigTag;
use EonX\EasyHttpClient\Bundle\Enum\ConfigParam as EasyHttpClientConfigParam;
use EonX\EasyLogging\Bundle\Enum\ConfigParam as EasyLoggingConfigParam;
use EonX\EasyRequestId\Bundle\Enum\ConfigParam;
use EonX\EasyWebhook\Bundle\Enum\ConfigParam as EasyWebhookConfigParam;
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

        $this->loadIfEnabled('easy_error_handler', EasyErrorHandlerConfigTag::class);
        $this->loadIfEnabled('easy_logging', EasyLoggingConfigParam::class);
        $this->loadIfEnabled('easy_http_client', EasyHttpClientConfigParam::class);
        $this->loadIfEnabled('easy_webhook', EasyWebhookConfigParam::class);
    }

    /**
     * @param class-string<\BackedEnum> $enum
     */
    private function loadIfEnabled(string $configName, string $enum): void
    {
        if (\enum_exists($enum) === false) {
            return;
        }

        // Load only if enabled in config
        if (($this->config[$configName] ?? true) === false) {
            return;
        }

        $this->containerConfigurator->import("config/$configName.php");
    }
}
