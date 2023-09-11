<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Laravel;

use EonX\EasyApiToken\Interfaces\Factories\ApiTokenDecoderFactoryInterface;
use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface as EasyBugsnagBridgeConstantsInterface;
use EonX\EasyLogging\Bridge\BridgeConstantsInterface as EasyLoggingBridgeConstantsInterface;
use EonX\EasySecurity\Authorization\AuthorizationMatrixFactory;
use EonX\EasySecurity\Authorization\CachedAuthorizationMatrixFactory;
use EonX\EasySecurity\Bridge\BridgeConstantsInterface;
use EonX\EasySecurity\Bridge\EasyBugsnag\SecurityContextClientConfigurator;
use EonX\EasySecurity\Bridge\Laravel\Listeners\FromRequestSecurityContextConfiguratorListener;
use EonX\EasySecurity\Bridge\Laravel\Middleware\FromRequestSecurityContextConfiguratorMiddleware;
use EonX\EasySecurity\Configurators\ApiTokenConfigurator;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixFactoryInterface;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface;
use EonX\EasySecurity\Interfaces\SecurityContextFactoryInterface;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use EonX\EasySecurity\SecurityContextFactory;
use EonX\EasySecurity\SecurityContextResolver;
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
        $this->app->singleton(BridgeConstantsInterface::SERVICE_AUTHORIZATION_MATRIX_CACHE, ArrayAdapter::class);

        $this->app->singleton(
            AuthorizationMatrixFactoryInterface::class,
            static fn (Container $app): AuthorizationMatrixFactoryInterface => new CachedAuthorizationMatrixFactory(
                $app->make(BridgeConstantsInterface::SERVICE_AUTHORIZATION_MATRIX_CACHE),
                new AuthorizationMatrixFactory(
                    $app->tagged(BridgeConstantsInterface::TAG_ROLES_PROVIDER),
                    $app->tagged(BridgeConstantsInterface::TAG_PERMISSIONS_PROVIDER)
                )
            )
        );

        $this->app->singleton(
            AuthorizationMatrixInterface::class,
            static fn (
                Container $app,
            ): AuthorizationMatrixInterface => $app->get(AuthorizationMatrixFactoryInterface::class)->create()
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
                SecurityContextConfiguratorInterface::SYSTEM_PRIORITY
            )
        );

        $this->app->tag(
            [ApiTokenConfigurator::class],
            [BridgeConstantsInterface::TAG_CONTEXT_CONFIGURATOR]
        );
    }

    private function registerEasyBugsnag(): void
    {
        if (\config('easy-security.easy_bugsnag', false) === false
            || \interface_exists(EasyBugsnagBridgeConstantsInterface::class) === false) {
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
            [EasyBugsnagBridgeConstantsInterface::TAG_CLIENT_CONFIGURATOR]
        );
    }

    private function registerLogger(): void
    {
        $this->app->singleton(
            BridgeConstantsInterface::SERVICE_LOGGER,
            static function (Container $app): LoggerInterface {
                $loggerParams = \interface_exists(EasyLoggingBridgeConstantsInterface::class)
                    ? [EasyLoggingBridgeConstantsInterface::KEY_CHANNEL => BridgeConstantsInterface::LOG_CHANNEL]
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
                ): FromRequestSecurityContextConfiguratorMiddleware => new FromRequestSecurityContextConfiguratorMiddleware(
                    $app->make(SecurityContextResolverInterface::class),
                    $app->tagged(BridgeConstantsInterface::TAG_CONTEXT_CONFIGURATOR)
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
                $app->tagged(BridgeConstantsInterface::TAG_CONTEXT_CONFIGURATOR)
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
                $app->make(BridgeConstantsInterface::SERVICE_LOGGER)
            )
        );

        // SecurityContextFactory
        $this->app->singleton(
            SecurityContextFactoryInterface::class,
            static fn (): SecurityContextFactoryInterface => new SecurityContextFactory()
        );
    }
}
