<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Laravel;

use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
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
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
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

        $contextServiceId = \config('easy-security.context_service_id');

        $this->registerApiTokenDecoder();
        $this->registerAuthorizationMatrix();
        $this->registerDefaultConfigurators();
        $this->registerEasyBugsnag();
        $this->registerLogger();
        $this->registerRequestConfigurators();
        $this->registerSecurityContext($contextServiceId);
    }

    private function registerApiTokenDecoder(): void
    {
        // Deprecated since 4.1, will be removed in 5.0. Use the ApiTokenDecoderFactoryInterface instead.
        $this->app->singleton(
            BridgeConstantsInterface::SERVICE_API_TOKEN_DECODER,
            static function (Container $app): ApiTokenDecoderInterface {
                return $app
                    ->make(ApiTokenDecoderFactoryInterface::class)
                    ->build(\config('easy-security.token_decoder'));
            }
        );
    }

    private function registerAuthorizationMatrix(): void
    {
        $this->app->singleton(BridgeConstantsInterface::SERVICE_AUTHORIZATION_MATRIX_CACHE, ArrayAdapter::class);

        $this->app->singleton(
            AuthorizationMatrixFactoryInterface::class,
            static function (Container $app): AuthorizationMatrixFactoryInterface {
                return new CachedAuthorizationMatrixFactory(
                    $app->make(BridgeConstantsInterface::SERVICE_AUTHORIZATION_MATRIX_CACHE),
                    new AuthorizationMatrixFactory(
                        $app->tagged(BridgeConstantsInterface::TAG_ROLES_PROVIDER),
                        $app->tagged(BridgeConstantsInterface::TAG_PERMISSIONS_PROVIDER)
                    )
                );
            }
        );

        $this->app->singleton(
            AuthorizationMatrixInterface::class,
            static function (Container $app): AuthorizationMatrixInterface {
                return $app->get(AuthorizationMatrixFactoryInterface::class)->create();
            }
        );
    }

    private function registerDefaultConfigurators(): void
    {
        if (\config('easy-security.use_default_configurators', true) === false) {
            return;
        }

        $this->app->singleton(ApiTokenConfigurator::class, static function (Container $app): ApiTokenConfigurator {
            return new ApiTokenConfigurator(
                $app->make(ApiTokenDecoderFactoryInterface::class),
                \config('easy-security.token_decoder'),
                SecurityContextConfiguratorInterface::SYSTEM_PRIORITY
            );
        });

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
            static function (Container $app): SecurityContextClientConfigurator {
                return new SecurityContextClientConfigurator(
                    $app->make(SecurityContextResolverInterface::class)
                );
            }
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
                static function (Container $app): FromRequestSecurityContextConfiguratorMiddleware {
                    return new FromRequestSecurityContextConfiguratorMiddleware(
                        $app->make(SecurityContextResolverInterface::class),
                        $app->tagged(BridgeConstantsInterface::TAG_CONTEXT_CONFIGURATOR)
                    );
                }
            );
            $this->app->middleware([FromRequestSecurityContextConfiguratorMiddleware::class]);

            return;
        }

        $this->app->singleton(
            FromRequestSecurityContextConfiguratorListener::class,
            static function (Container $app): FromRequestSecurityContextConfiguratorListener {
                return new FromRequestSecurityContextConfiguratorListener(
                    $app->make(SecurityContextResolverInterface::class),
                    $app->tagged(BridgeConstantsInterface::TAG_CONTEXT_CONFIGURATOR)
                );
            }
        );
    }

    private function registerSecurityContext(string $contextServiceId): void
    {
        // Resolver
        $this->app->singleton(
            SecurityContextResolverInterface::class,
            static function (Container $app): SecurityContextResolverInterface {
                return new SecurityContextResolver(
                    $app->make(AuthorizationMatrixFactoryInterface::class),
                    $app->make(SecurityContextFactoryInterface::class),
                    $app->make(BridgeConstantsInterface::SERVICE_LOGGER)
                );
            }
        );

        // SecurityContextFactory
        $this->app->singleton(
            SecurityContextFactoryInterface::class,
            static function (): SecurityContextFactoryInterface {
                return new SecurityContextFactory();
            }
        );

        // SecurityContext
        $this->app->singleton($contextServiceId, static function (Container $app): SecurityContextInterface {
            return $app->make(SecurityContextResolverInterface::class)->resolveContext();
        });
    }
}
