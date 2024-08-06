<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Laravel;

use EonX\EasyApiToken\Common\Factory\ApiTokenDecoderFactoryInterface;
use EonX\EasyBugsnag\Bundle\Enum\ConfigTag as EasyBugsnagConfigTag;
use EonX\EasyLogging\Bundle\Enum\BundleParam as EasyLoggingBundleParam;
use EonX\EasySecurity\Authorization\Factory\AuthorizationMatrixFactory;
use EonX\EasySecurity\Authorization\Factory\AuthorizationMatrixFactoryInterface;
use EonX\EasySecurity\Authorization\Factory\CachedAuthorizationMatrixFactory;
use EonX\EasySecurity\Authorization\Provider\AuthorizationMatrixProviderInterface;
use EonX\EasySecurity\Bundle\Enum\BundleParam;
use EonX\EasySecurity\Bundle\Enum\ConfigServiceId;
use EonX\EasySecurity\Bundle\Enum\ConfigTag;
use EonX\EasySecurity\Common\Configurator\ApiTokenConfigurator;
use EonX\EasySecurity\Common\Factory\SecurityContextFactory;
use EonX\EasySecurity\Common\Factory\SecurityContextFactoryInterface;
use EonX\EasySecurity\Common\Resolver\SecurityContextResolver;
use EonX\EasySecurity\Common\Resolver\SecurityContextResolverInterface;
use EonX\EasySecurity\EasyBugsnag\Configurator\SecurityContextClientConfigurator;
use EonX\EasySecurity\Laravel\Listeners\FromRequestSecurityContextConfiguratorListener;
use EonX\EasySecurity\Laravel\Middleware\FromRequestSecurityContextConfiguratorMiddleware;
use Illuminate\Contracts\Container\Container;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class EasySecurityServiceProvider extends ServiceProvider
{
    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-security.php' => \base_path('config/easy-security.php'),
        ]);

        $this->app->make('events')
            ->listen(RouteMatched::class, FromRequestSecurityContextConfiguratorListener::class);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-security.php', 'easy-security');

        $this->registerAuthorizationMatrix();
        $this->registerDefaultConfigurators();
        $this->registerEasyBugsnag();
        $this->registerLogger();
        $this->registerRequestConfigurators();
        $this->registerSecurityContext();
    }

    private function registerAuthorizationMatrix(): void
    {
        $this->app->singleton(ConfigServiceId::AuthorizationMatrixCache->value, ArrayAdapter::class);

        $this->app->singleton(
            AuthorizationMatrixFactoryInterface::class,
            static fn (Container $app): AuthorizationMatrixFactoryInterface => new CachedAuthorizationMatrixFactory(
                $app->make(ConfigServiceId::AuthorizationMatrixCache->value),
                new AuthorizationMatrixFactory(
                    $app->tagged(ConfigTag::RolesProvider->value),
                    $app->tagged(ConfigTag::PermissionsProvider->value)
                )
            )
        );

        $this->app->singleton(
            AuthorizationMatrixProviderInterface::class,
            static fn (
                Container $app,
            ): AuthorizationMatrixProviderInterface => $app->get(AuthorizationMatrixFactoryInterface::class)->create()
        );
    }

    private function registerDefaultConfigurators(): void
    {
        if (\config('easy-security.use_default_configurators', true) === false) {
            return;
        }

        $this->app->singleton(
            ApiTokenConfigurator::class,
            static fn (Container $app): ApiTokenConfigurator => new ApiTokenConfigurator(
                $app->make(ApiTokenDecoderFactoryInterface::class),
                \config('easy-security.token_decoder'),
                \config('easy-security.default_configurators_priority')
            )
        );

        $this->app->tag(
            [ApiTokenConfigurator::class],
            [ConfigTag::ContextConfigurator->value]
        );
    }

    private function registerEasyBugsnag(): void
    {
        if (\config('easy-security.easy_bugsnag', false) === false
            || \enum_exists(EasyBugsnagConfigTag::class) === false) {
            return;
        }

        $this->app->singleton(
            SecurityContextClientConfigurator::class,
            static fn (Container $app): SecurityContextClientConfigurator => new SecurityContextClientConfigurator(
                $app->make(SecurityContextResolverInterface::class)
            )
        );
        $this->app->tag(
            SecurityContextClientConfigurator::class,
            [EasyBugsnagConfigTag::ClientConfigurator->value]
        );
    }

    private function registerLogger(): void
    {
        $this->app->singleton(
            ConfigServiceId::Logger->value,
            static function (Container $app): LoggerInterface {
                $loggerParams = \enum_exists(EasyLoggingBundleParam::class)
                    ? [EasyLoggingBundleParam::KeyChannel->value => BundleParam::LogChannel->value]
                    : [];

                return $app->make(LoggerInterface::class, $loggerParams);
            }
        );
    }

    private function registerRequestConfigurators(): void
    {
        if ($this->app instanceof LumenApplication) {
            $this->app->singleton(
                FromRequestSecurityContextConfiguratorMiddleware::class,
                static fn (
                    Container $app,
                ): FromRequestSecurityContextConfiguratorMiddleware => new
                FromRequestSecurityContextConfiguratorMiddleware(
                    $app->make(SecurityContextResolverInterface::class),
                    $app->tagged(ConfigTag::ContextConfigurator->value)
                )
            );
            $this->app->middleware([FromRequestSecurityContextConfiguratorMiddleware::class]);

            return;
        }

        $this->app->singleton(
            FromRequestSecurityContextConfiguratorListener::class,
            static fn (
                Container $app,
            ): FromRequestSecurityContextConfiguratorListener => new FromRequestSecurityContextConfiguratorListener(
                $app->make(SecurityContextResolverInterface::class),
                $app->tagged(ConfigTag::ContextConfigurator->value)
            )
        );
    }

    private function registerSecurityContext(): void
    {
        // Resolver
        $this->app->singleton(
            SecurityContextResolverInterface::class,
            static fn (Container $app): SecurityContextResolverInterface => new SecurityContextResolver(
                $app->make(AuthorizationMatrixFactoryInterface::class),
                $app->make(SecurityContextFactoryInterface::class),
                $app->make(ConfigServiceId::Logger->value)
            )
        );

        // SecurityContextFactory
        $this->app->singleton(
            SecurityContextFactoryInterface::class,
            static fn (): SecurityContextFactoryInterface => new SecurityContextFactory()
        );
    }
}
