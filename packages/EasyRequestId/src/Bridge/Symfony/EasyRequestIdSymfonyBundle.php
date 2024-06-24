<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Symfony;

use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface as EasyErrorHandlerBridgeConstantsInterface;
use EonX\EasyHttpClient\Bundle\Enum\ConfigParam as EasyHttpClientConfigParam;
use EonX\EasyLogging\Bundle\Enum\ConfigParam as EasyLoggingConfigParam;
use EonX\EasyRequestId\Bridge\BridgeConstantsInterface;
use EonX\EasyWebhook\Bridge\BridgeConstantsInterface as EasyWebhookBridgeConstantsInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyRequestIdSymfonyBundle extends AbstractBundle
{
    protected string $extensionAlias = 'easy_request_id';

    private array $config;

    private ContainerConfigurator $containerConfigurator;

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__ . '/Resources/config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // HTTP headers
        $container
            ->parameters()
            ->set(
                BridgeConstantsInterface::PARAM_HTTP_HEADER_CORRELATION_ID,
                $config['http_headers']['correlation_id']
            );

        $container
            ->parameters()
            ->set(
                BridgeConstantsInterface::PARAM_HTTP_HEADER_REQUEST_ID,
                $config['http_headers']['request_id']
            );

        $container->import(__DIR__ . '/Resources/config/services.php');

        $this->config = $config;
        $this->containerConfigurator = $container;

        $this->loadIfEnabled('easy_error_handler', EasyErrorHandlerBridgeConstantsInterface::class);
        $this->loadIfEnabled('easy_logging', EasyLoggingConfigParam::class);
        $this->loadIfEnabled('easy_http_client', EasyHttpClientConfigParam::class);
        $this->loadIfEnabled('easy_webhook', EasyWebhookBridgeConstantsInterface::class);
    }

    private function loadIfEnabled(string $configName, ?string $enum = null): void
    {
        // Load only if interface exists
        // @todo Remove \interface_exists after migration to new structure
        if ($enum !== null && (\enum_exists($enum) || \interface_exists($enum)) === false) {
            return;
        }

        // Load only if enabled in config
        if (($this->config[$configName] ?? true) === false) {
            return;
        }

        $this->containerConfigurator->import(__DIR__ . "/Resources/config/{$configName}.php");
    }
}
