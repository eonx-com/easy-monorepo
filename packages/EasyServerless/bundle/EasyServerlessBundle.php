<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Bundle;

use EonX\EasyServerless\Bundle\CompilerPass\DecoratePathPackagesToUseUrlCompilerPass;
use EonX\EasyServerless\Bundle\Enum\ConfigParam;
use Monolog\Logger;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyServerlessBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new DecoratePathPackagesToUseUrlCompilerPass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container
            ->parameters()
            ->set(ConfigParam::AssetsSeparateDomainEnabled->value, $config['assets_separate_domain']['enabled'])
            ->set(ConfigParam::AssetsSeparateDomainUrl->value, $config['assets_separate_domain']['url']);

        if (\class_exists(Logger::class)) {
            $container->import('config/monolog.php');
        }
    }
}
