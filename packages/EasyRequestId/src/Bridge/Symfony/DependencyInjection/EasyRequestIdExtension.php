<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Symfony\DependencyInjection;

use EonX\EasyRequestId\Bridge\BridgeConstantsInterface;
use EonX\EasyRequestId\Interfaces\CorrelationIdResolverInterface;
use EonX\EasyRequestId\Interfaces\RequestIdResolverInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyRequestIdExtension extends Extension
{
    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new PhpFileLoader($container, new FileLocator([__DIR__ . '/../Resources/config']));

        $container->setParameter(
            BridgeConstantsInterface::PARAM_DEFAULT_CORRELATION_ID_HEADER,
            $config['default_correlation_id_header']
        );
        $container->setParameter(
            BridgeConstantsInterface::PARAM_DEFAULT_REQUEST_ID_HEADER,
            $config['default_request_id_header']
        );

        $container
            ->registerForAutoconfiguration(CorrelationIdResolverInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_CORRELATION_ID_RESOLVER);

        $container
            ->registerForAutoconfiguration(RequestIdResolverInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_REQUEST_ID_RESOLVER);

        $loader->load('services.php');

        if ($config['default_resolver'] ?? true) {
            $loader->load('default_resolver.php');
        }
    }
}
