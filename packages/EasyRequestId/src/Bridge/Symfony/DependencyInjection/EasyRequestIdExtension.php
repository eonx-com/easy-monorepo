<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Symfony\DependencyInjection;

use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface as EasyErrorHandlerBridgeConstantsInterface;
use EonX\EasyHttpClient\Bridge\BridgeConstantsInterface as EasyHttpClientBridgeConstantsInterface;
use EonX\EasyLogging\Bridge\BridgeConstantsInterface as EasyLoggingBridgeConstantsInterface;
use EonX\EasyRequestId\Bridge\BridgeConstantsInterface;
use EonX\EasyWebhook\Bridge\BridgeConstantsInterface as EasyWebhookBridgeConstantsInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyRequestIdExtension extends Extension
{
    /**
     * @var mixed[]
     */
    private $config;

    /**
     * @var \Symfony\Component\Config\Loader\LoaderInterface
     */
    private $loader;

    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->config = $config = $this->processConfiguration(new Configuration(), $configs);
        $this->loader = $loader = new PhpFileLoader($container, new FileLocator([__DIR__ . '/../Resources/config']));

        // HTTP headers
        $container->setParameter(
            BridgeConstantsInterface::PARAM_HTTP_HEADER_CORRELATION_ID,
            $config['http_headers']['correlation_id'],
        );

        $container->setParameter(
            BridgeConstantsInterface::PARAM_HTTP_HEADER_REQUEST_ID,
            $config['http_headers']['request_id'],
        );

        $loader->load('services.php');

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

        $this->loader->load(\sprintf('%s.php', $configName));
    }
}
