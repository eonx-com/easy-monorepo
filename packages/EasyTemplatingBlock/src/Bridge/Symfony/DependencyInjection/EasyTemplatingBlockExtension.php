<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Bridge\Symfony\DependencyInjection;

use EonX\EasyTemplatingBlock\Bridge\BridgeConstantsInterface;
use EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockProviderInterface;
use EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockRendererInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Twig\Environment as TwigEnvironment;

final class EasyTemplatingBlockExtension extends Extension
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new PhpFileLoader($container, new FileLocator([__DIR__ . '/../Resources/config']));

        $container
            ->registerForAutoconfiguration(TemplatingBlockProviderInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_TEMPLATING_BLOCK_PROVIDER);

        $container
            ->registerForAutoconfiguration(TemplatingBlockRendererInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_TEMPLATING_BLOCK_RENDERER);

        $container->setParameter(BridgeConstantsInterface::PARAM_IS_DEBUG, (bool)($config['is_debug'] ?? false));

        $loader->load('services.php');

        if (($config['use_twig'] ?? true) && \class_exists(TwigEnvironment::class)) {
            $loader->load('twig.php');
        }
    }
}
