<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Bundle;

use EonX\EasyTemplatingBlock\Bundle\Enum\ConfigParam;
use EonX\EasyTemplatingBlock\Bundle\Enum\ConfigTag;
use EonX\EasyTemplatingBlock\Common\Provider\TemplatingBlockProviderInterface;
use EonX\EasyTemplatingBlock\Common\Renderer\TemplatingBlockRendererInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Twig\Environment as TwigEnvironment;

final class EasyTemplatingBlockBundle extends AbstractBundle
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
        $builder
            ->registerForAutoconfiguration(TemplatingBlockProviderInterface::class)
            ->addTag(ConfigTag::TemplatingBlockProvider->value);

        $builder
            ->registerForAutoconfiguration(TemplatingBlockRendererInterface::class)
            ->addTag(ConfigTag::TemplatingBlockRenderer->value);

        $container
            ->parameters()
            ->set(ConfigParam::IsDebug->value, (bool)($config['is_debug'] ?? false));

        $container->import('config/services.php');

        if (($config['use_twig'] ?? true) && \class_exists(TwigEnvironment::class)) {
            $container->import('config/twig.php');
        }
    }
}
