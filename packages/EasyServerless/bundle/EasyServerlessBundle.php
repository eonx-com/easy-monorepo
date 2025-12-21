<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Bundle;

use EonX\EasyServerless\Bundle\CompilerPass\DecoratePathPackagesToUseUrlCompilerPass;
use EonX\EasyServerless\Bundle\CompilerPass\PersistentSystemCacheCompilerPass;
use EonX\EasyServerless\Bundle\Enum\ConfigParam;
use EonX\EasyServerless\Bundle\Enum\ConfigTag;
use EonX\EasyServerless\Health\Checker\HealthCheckerInterface;
use EonX\EasyServerless\State\Checker\StateCheckerInterface;
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

        $container
            ->addCompilerPass(new DecoratePathPackagesToUseUrlCompilerPass())
            ->addCompilerPass(new PersistentSystemCacheCompilerPass(), priority: -33);
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

        $builder
            ->registerForAutoconfiguration(HealthCheckerInterface::class)
            ->addTag(ConfigTag::HealthChecker->value);

        $builder
            ->registerForAutoconfiguration(StateCheckerInterface::class)
            ->addTag(ConfigTag::StateChecker->value);

        $container->import('config/services.php');

        if ($config['health']['enabled']) {
            $container->import('config/health.php');
        }

        if ($config['state'] && $config['state']['check']) {
            $container->import('config/state.php');
        }

        if ($this->isBundleEnabled('EasyAdminBundle', $builder)) {
            $container->import('config/easy_admin.php');
        }

        if ($this->isBundleEnabled('EasyBugsnagBundle', $builder)) {
            $container->import('config/easy_bugsnag.php');
        }

        if ($this->isBundleEnabled('DoctrineBundle', $builder)) {
            $container->import('config/doctrine.php');
        }

        if (\class_exists(Logger::class) && $config['monolog']['enabled']) {
            $container->import('config/monolog.php');
        }
    }

    private function isBundleEnabled(string $bundleName, ContainerBuilder $builder): bool
    {
        /** @var array $bundles */
        $bundles = $builder->getParameter('kernel.bundles');

        return isset($bundles[$bundleName]);
    }
}
