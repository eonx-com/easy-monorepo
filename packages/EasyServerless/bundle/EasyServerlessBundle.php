<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Bundle;

use Bref\Bref;
use EasyCorp\Bundle\EasyAdminBundle\EventListener\AdminRouterSubscriber;
use EonX\EasyServerless\Aws\Helper\LambdaContextHelper;
use EonX\EasyServerless\Bundle\CompilerPass\AppMetricLoggerFallbackCompilerPass;
use EonX\EasyServerless\Bundle\CompilerPass\DecoratePathPackagesToUseUrlCompilerPass;
use EonX\EasyServerless\Bundle\CompilerPass\PersistentSystemCacheCompilerPass;
use EonX\EasyServerless\Bundle\CompilerPass\SnapStartServiceClosuresCompilerPass;
use EonX\EasyServerless\Bundle\Enum\ConfigParam;
use EonX\EasyServerless\Bundle\Enum\ConfigTag;
use EonX\EasyServerless\Health\Checker\HealthCheckerInterface;
use EonX\EasyServerless\SnapStart\Enum\SnapStartTag;
use EonX\EasyServerless\SnapStart\Hook\AfterRestoreHookInterface;
use EonX\EasyServerless\SnapStart\Subscriber\SnapStartLifecycleSubscriber;
use EonX\EasyServerless\SnapStart\Warmer\SnapStartWarmerInterface;
use EonX\EasyServerless\State\Checker\StateCheckerInterface;
use Monolog\Logger;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyServerlessBundle extends AbstractBundle
{
    public function boot(): void
    {
        parent::boot();

        // Bref resolves the handler, which boots the kernel, before it dispatches the afterStartup event
        if (LambdaContextHelper::inSnapStartLambda() === false) {
            return;
        }

        $snapStartLifecycleSubscriber = $this->container?->get(
            SnapStartLifecycleSubscriber::class,
            ContainerInterface::NULL_ON_INVALID_REFERENCE
        );

        if ($snapStartLifecycleSubscriber instanceof SnapStartLifecycleSubscriber) {
            Bref::events()->subscribe($snapStartLifecycleSubscriber);
        }
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container
            // Must run after LoggerPass (-32) which registers the default application logger when there is none
            ->addCompilerPass(new AppMetricLoggerFallbackCompilerPass(), priority: -33)
            ->addCompilerPass(new DecoratePathPackagesToUseUrlCompilerPass())
            // Must run after CachePoolPass (32) adds the "kernel.reset" tags, and before ResettableServicePass (-32)
            // collects them into "services_resetter"
            ->addCompilerPass(new PersistentSystemCacheCompilerPass(), priority: -31)
            // Must run after ResolveTaggedIteratorArgumentPass (optimization) sorts the tagged services
            ->addCompilerPass(new SnapStartServiceClosuresCompilerPass(), PassConfig::TYPE_BEFORE_REMOVING);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container
            ->parameters()
            ->set(ConfigParam::AppMetricNamespace->value, $config['app_metric']['namespace'])
            ->set(ConfigParam::AssetsSeparateDomainEnabled->value, $config['assets_separate_domain']['enabled'])
            ->set(ConfigParam::AssetsSeparateDomainUrl->value, $config['assets_separate_domain']['url']);

        $builder
            ->registerForAutoconfiguration(HealthCheckerInterface::class)
            ->addTag(ConfigTag::HealthChecker->value);

        $builder
            ->registerForAutoconfiguration(StateCheckerInterface::class)
            ->addTag(ConfigTag::StateChecker->value);

        $builder
            ->registerForAutoconfiguration(SnapStartWarmerInterface::class)
            ->addTag(SnapStartTag::Warmer->value);

        $builder
            ->registerForAutoconfiguration(AfterRestoreHookInterface::class)
            ->addTag(SnapStartTag::AfterRestoreHook->value);

        $container->import('config/services.php');

        if ($config['app_metric']['enabled']) {
            $container->import('config/app_metric.php');
        }

        if ($config['console']['enabled']) {
            $container->import('config/console.php');
        }

        if ($config['datadog']['enabled']) {
            $container->import('config/datadog.php');
        }

        if ($config['health']['enabled']) {
            $container->import('config/health.php');
        }

        if ($config['security_headers']['enabled']) {
            $container->import('config/security_headers.php');
        }

        if ($config['state'] && $config['state']['check']) {
            $container->import('config/state.php');
        }

        // Only EasyAdmin 4.21+ keeps the pretty URLs state in AdminRouterSubscriber, it is stateless since 5.0
        if ($this->isBundleEnabled('EasyAdminBundle', $builder)
            && \property_exists(AdminRouterSubscriber::class, 'requestAlreadyProcessedAsPrettyUrl')) {
            $container->import('config/easy_admin.php');
        }

        if ($this->isBundleEnabled('EasyBugsnagBundle', $builder)) {
            $container->import('config/easy_bugsnag.php');
        }

        if ($this->isBundleEnabled('EasyPaginationBundle', $builder)) {
            $container->import('config/easy_pagination.php');
        }

        if ($this->isBundleEnabled('DoctrineBundle', $builder)) {
            $container->import('config/doctrine.php');
        }

        if ($this->isBundleEnabled('BrefMessengerBundle', $builder)) {
            $container->import('config/messenger.php');

            if ($config['sqs']['reset_services']['enabled']) {
                $container->import('config/sqs_reset_services.php');
            }

            if ($config['state'] && $config['state']['check']) {
                $container->import('config/messenger_state_checker.php');
            }
        }

        if (\class_exists(Logger::class) && $config['monolog']['enabled']) {
            $container->import('config/monolog.php');
        }

        if ($config['snap_start']['enabled']) {
            $this->loadSnapStart($config['snap_start'], $container, $builder);
        }
    }

    private function isBundleEnabled(string $bundleName, ContainerBuilder $builder): bool
    {
        /** @var array $bundles */
        $bundles = $builder->getParameter('kernel.bundles');

        return isset($bundles[$bundleName]);
    }

    private function loadSnapStart(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('config/snap_start.php');

        if ($config['warmers']['class_map']['enabled']) {
            $container
                ->parameters()
                ->set(
                    ConfigParam::SnapStartClassMapWarmerNamespaces->value,
                    $config['warmers']['class_map']['namespaces']
                );

            $container->import('config/snap_start_class_map.php');
        }

        if ($config['warmers']['http_request']['enabled']) {
            $container
                ->parameters()
                ->set(ConfigParam::SnapStartHttpRequestWarmerHost->value, $config['warmers']['http_request']['host'])
                ->set(ConfigParam::SnapStartHttpRequestWarmerPath->value, $config['warmers']['http_request']['path']);

            $container->import('config/snap_start_http_request.php');
        }

        if ($this->isBundleEnabled('DoctrineBundle', $builder)) {
            $container->import('config/snap_start_doctrine.php');
        }
    }
}
