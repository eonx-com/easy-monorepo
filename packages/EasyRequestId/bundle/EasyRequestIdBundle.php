<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Bundle;

use EonX\EasyRequestId\Bundle\Enum\ConfigParam;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyRequestIdBundle extends AbstractBundle
{
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
        $container
            ->parameters()
            ->set(ConfigParam::HttpHeaderCorrelationId->value, $config['http_headers']['correlation_id'])
            ->set(ConfigParam::HttpHeaderRequestId->value, $config['http_headers']['request_id']);

        $container->import('config/services.php');

        if ($config['easy_error_handler']['enabled']) {
            $container->import('config/easy_error_handler.php');
        }

        if ($config['easy_logging']['enabled']) {
            $container->import('config/easy_logging.php');
        }

        if ($config['easy_http_client']['enabled']) {
            $container->import('config/easy_http_client.php');
        }

        if ($config['easy_webhook']['enabled']) {
            $container->import('config/easy_webhook.php');
        }
    }
}
