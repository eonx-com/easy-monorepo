<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Symfony\DependencyInjection;

use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface as EasyBugsnagBridgeConstantsInterface;
use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface as EasyErrorHandlerBridgeConstantsInterface;
use EonX\EasyLogging\Bridge\BridgeConstantsInterface as EasyLoggingBridgeConstantsInterface;
use EonX\EasyRequestId\Bridge\BridgeConstantsInterface;
use EonX\EasyRequestId\Interfaces\CorrelationIdResolverInterface;
use EonX\EasyRequestId\Interfaces\RequestIdResolverInterface;
use EonX\EasyWebhook\Bridge\BridgeConstantsInterface as EasyWebhookBridgeConstantsInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyRequestIdExtension extends Extension
{
    /**
     * @var string[]
     */
    protected static $params = [
        BridgeConstantsInterface::PARAM_CORRELATION_ID_KEY => 'correlation_id_key',
        BridgeConstantsInterface::PARAM_DEFAULT_CORRELATION_ID_HEADER => 'default_correlation_id_header',
        BridgeConstantsInterface::PARAM_DEFAULT_REQUEST_ID_HEADER => 'default_request_id_header',
        BridgeConstantsInterface::PARAM_REQUEST_ID_KEY => 'request_id_key',
    ];

    /**
     * @var string[]
     */
    protected static $tags = [
        CorrelationIdResolverInterface::class => BridgeConstantsInterface::TAG_CORRELATION_ID_RESOLVER,
        RequestIdResolverInterface::class => BridgeConstantsInterface::TAG_REQUEST_ID_RESOLVER,
    ];

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

        foreach (static::$params as $param => $configName) {
            $container->setParameter($param, $config[$configName]);
        }

        foreach (static::$tags as $interface => $tag) {
            $container->registerForAutoconfiguration($interface)
                ->addTag($tag);
        }

        $loader->load('services.php');

        $this->loadIfEnabled('default_resolver');
        $this->loadIfEnabled('easy_bugsnag', EasyBugsnagBridgeConstantsInterface::class);
        $this->loadIfEnabled('easy_error_handler', EasyErrorHandlerBridgeConstantsInterface::class);
        $this->loadIfEnabled('easy_logging', EasyLoggingBridgeConstantsInterface::class);
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
