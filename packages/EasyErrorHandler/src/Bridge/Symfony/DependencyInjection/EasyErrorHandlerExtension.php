<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\DependencyInjection;

use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorReporterProviderInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyErrorHandlerExtension extends Extension
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

        $container->setParameter(BridgeConstantsInterface::PARAM_IS_VERBOSE, $config['verbose']);
        $container->setParameter(BridgeConstantsInterface::PARAM_RESPONSE_KEYS, $config['response']);
        $container->setParameter(BridgeConstantsInterface::PARAM_TRANSLATION_DOMAIN, $config['translation_domain']);

        $container
            ->registerForAutoconfiguration(ErrorReporterProviderInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_ERROR_REPORTER_PROVIDER);

        $container
            ->registerForAutoconfiguration(ErrorResponseBuilderProviderInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_ERROR_RESPONSE_BUILDER_PROVIDER);

        $loader->load('services.php');

        if ($config['user_default_builders'] ?? true) {
            $loader->load('default_builders.php');
        }

        if ($config['user_default_reporters'] ?? true) {
            $loader->load('default_reporters.php');
        }
    }
}
