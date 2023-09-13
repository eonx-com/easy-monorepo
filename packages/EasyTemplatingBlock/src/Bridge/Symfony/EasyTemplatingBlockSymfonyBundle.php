<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Bridge\Symfony;

use EonX\EasyTemplatingBlock\Bridge\BridgeConstantsInterface;
use EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockProviderInterface;
use EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockRendererInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Twig\Environment as TwigEnvironment;

final class EasyTemplatingBlockSymfonyBundle extends AbstractBundle
{
    protected string $extensionAlias = 'easy_templating_block';

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
        $builder
            ->registerForAutoconfiguration(TemplatingBlockProviderInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_TEMPLATING_BLOCK_PROVIDER);

        $builder
            ->registerForAutoconfiguration(TemplatingBlockRendererInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_TEMPLATING_BLOCK_RENDERER);

        $container
            ->parameters()
            ->set(BridgeConstantsInterface::PARAM_IS_DEBUG, (bool)($config['is_debug'] ?? false));

        $container->import(__DIR__ . '/Resources/config/services.php');

        if (($config['use_twig'] ?? true) && \class_exists(TwigEnvironment::class)) {
            $container->import(__DIR__ . '/Resources/config/twig.php');
        }
    }
}
