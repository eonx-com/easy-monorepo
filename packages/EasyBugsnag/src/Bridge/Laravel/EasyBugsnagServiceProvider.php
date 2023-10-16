<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Laravel;

use Bugsnag\Client;
use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface;
use EonX\EasyBugsnag\Bridge\EasyUtils\Exceptions\EasyUtilsNotInstalledException;
use EonX\EasyBugsnag\Bridge\EasyUtils\SensitiveDataSanitizerConfigurator;
use EonX\EasyBugsnag\Bridge\Laravel\Request\LaravelRequestResolver;
use EonX\EasyBugsnag\Bridge\Laravel\Session\SessionTrackingConfigurator;
use EonX\EasyBugsnag\Bridge\Laravel\Session\SessionTrackingListener;
use EonX\EasyBugsnag\Bridge\Laravel\Session\SessionTrackingMiddleware;
use EonX\EasyBugsnag\Bridge\Laravel\Session\SessionTrackingQueueListener;
use EonX\EasyBugsnag\ClientFactory;
use EonX\EasyBugsnag\Configurators\AppNameConfigurator;
use EonX\EasyBugsnag\Configurators\AwsEcsFargateConfigurator;
use EonX\EasyBugsnag\Configurators\BasicsConfigurator;
use EonX\EasyBugsnag\Configurators\RuntimeVersionConfigurator;
use EonX\EasyBugsnag\Configurators\UnhandledClientConfigurator;
use EonX\EasyBugsnag\Interfaces\AppNameResolverInterface;
use EonX\EasyBugsnag\Interfaces\ClientFactoryInterface;
use EonX\EasyBugsnag\Resolvers\DefaultAppNameResolver;
use EonX\EasyBugsnag\Session\SessionTracker;
use EonX\EasyBugsnag\Shutdown\ShutdownStrategy;
use EonX\EasyUtils\SensitiveData\SensitiveDataSanitizerInterface;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Lumen\Application as LumenApplication;

final class EasyBugsnagServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-bugsnag.php' => \base_path('config/easy-bugsnag.php'),
        ]);
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-bugsnag.php', 'easy-bugsnag');

        if (\config('easy-bugsnag.enabled', true) === false) {
            return;
        }

        $this->registerAppName();
        $this->registerAwsEcsFargate();
        $this->registerClient();
        $this->registerConfigurators();
        $this->registerRequestResolver();
        $this->registerSensitiveDataSanitizer();
        $this->registerSessionTracking();
        $this->registerShutdownStrategy();
    }

    private function registerAppName(): void
    {
        if (\config('easy-bugsnag.app_name.enabled', false)) {
            $this->app->singleton(
                AppNameResolverInterface::class,
                static fn (): AppNameResolverInterface => new DefaultAppNameResolver(
                    \config('easy-bugsnag.app_name.env_var')
                )
            );

            $this->app->singleton(
                AppNameConfigurator::class,
                static fn (Container $app): AppNameConfigurator => new AppNameConfigurator(
                    $app->make(AppNameResolverInterface::class)
                )
            );
            $this->app->tag(AppNameConfigurator::class, [BridgeConstantsInterface::TAG_CLIENT_CONFIGURATOR]);
        }
    }

    private function registerAwsEcsFargate(): void
    {
        if (\config('easy-bugsnag.aws_ecs_fargate.enabled', false)) {
            $this->app->singleton(
                AwsEcsFargateConfigurator::class,
                static fn (): AwsEcsFargateConfigurator => new AwsEcsFargateConfigurator(
                    \config('easy-bugsnag.aws_ecs_fargate.meta_storage_filename'),
                    \config('easy-bugsnag.aws_ecs_fargate.meta_url')
                )
            );
            $this->app->tag(AwsEcsFargateConfigurator::class, [BridgeConstantsInterface::TAG_CLIENT_CONFIGURATOR]);
        }
    }

    private function registerClient(): void
    {
        // Client Factory + Client
        $this->app->singleton(
            ClientFactoryInterface::class,
            static fn (Container $app): ClientFactoryInterface => (new ClientFactory())
                ->setConfigurators($app->tagged(BridgeConstantsInterface::TAG_CLIENT_CONFIGURATOR))
                ->setRequestResolver($app->make(BridgeConstantsInterface::SERVICE_REQUEST_RESOLVER))
                ->setShutdownStrategy($app->make(BridgeConstantsInterface::SERVICE_SHUTDOWN_STRATEGY))
        );

        $this->app->singleton(
            Client::class,
            static fn (Container $app): Client => $app->make(ClientFactoryInterface::class)
                ->create(\config('easy-bugsnag.api_key'))
        );
    }

    private function registerConfigurators(): void
    {
        if (\config('easy-bugsnag.use_default_configurators', true) === false) {
            return;
        }

        $this->app->singleton(
            BasicsConfigurator::class,
            static fn (): BasicsConfigurator => new BasicsConfigurator(
                \config('easy-bugsnag.project_root'),
                \config('easy-bugsnag.strip_path'),
                \config('easy-bugsnag.release_stage')
            )
        );
        $this->app->tag(BasicsConfigurator::class, [BridgeConstantsInterface::TAG_CLIENT_CONFIGURATOR]);

        $this->app->singleton(
            RuntimeVersionConfigurator::class,
            static function (Container $app): RuntimeVersionConfigurator {
                /** @var \Illuminate\Contracts\Foundation\Application $app */
                $version = $app->version();
                $runtime = Str::contains($version, 'Lumen') ? 'lumen' : 'laravel';

                return new RuntimeVersionConfigurator($runtime, $version);
            }
        );
        $this->app->tag(RuntimeVersionConfigurator::class, [BridgeConstantsInterface::TAG_CLIENT_CONFIGURATOR]);

        $this->app->singleton(
            UnhandledClientConfigurator::class,
            static fn (): UnhandledClientConfigurator => new UnhandledClientConfigurator(
                \config('easy-bugsnag.handled_exceptions')
            )
        );
        $this->app->tag(UnhandledClientConfigurator::class, [BridgeConstantsInterface::TAG_CLIENT_CONFIGURATOR]);
    }

    private function registerRequestResolver(): void
    {
        // Request Resolver
        $this->app->singleton(BridgeConstantsInterface::SERVICE_REQUEST_RESOLVER, LaravelRequestResolver::class);
    }

    private function registerSensitiveDataSanitizer(): void
    {
        if (\config('easy-bugsnag.sensitive_data_sanitizer.enabled', true)) {
            $this->app->singleton(
                SensitiveDataSanitizerConfigurator::class,
                static function (Container $app): SensitiveDataSanitizerConfigurator {
                    $sanitizerId = SensitiveDataSanitizerInterface::class;

                    if (\interface_exists($sanitizerId) === false || $app->has($sanitizerId) === false) {
                        throw new EasyUtilsNotInstalledException(
                            'To use sensitive data sanitization, the package eonx-com/easy-utils must be installed,
                            and its service provider must be registered'
                        );
                    }

                    return new SensitiveDataSanitizerConfigurator($app->make($sanitizerId));
                }
            );
            $this->app->tag(
                SensitiveDataSanitizerConfigurator::class,
                [BridgeConstantsInterface::TAG_CLIENT_CONFIGURATOR]
            );
        }
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function registerSessionTracking(): void
    {
        if (\config('easy-bugsnag.session_tracking.enabled', false)) {
            $this->app->singleton(
                SessionTracker::class,
                static fn (Container $app): SessionTracker => new SessionTracker(
                    $app->make(Client::class),
                    \config('easy-bugsnag.session_tracking.exclude_urls', []),
                    \config('easy-bugsnag.session_tracking.exclude_urls_delimiter', '#')
                )
            );

            $this->app->singleton(
                BridgeConstantsInterface::SERVICE_SESSION_TRACKING_CACHE,
                static fn (Container $app): Repository => $app->make('cache')
                    ->store(\config('easy-bugsnag.session_tracking.cache_store', 'file'))
            );

            $this->app->singleton(
                SessionTrackingConfigurator::class,
                static fn (Container $app): SessionTrackingConfigurator => new SessionTrackingConfigurator(
                    $app->make(BridgeConstantsInterface::SERVICE_SESSION_TRACKING_CACHE),
                    \config('easy-bugsnag.session_tracking.cache_expires_after', 3600)
                )
            );
            $this->app->tag(SessionTrackingConfigurator::class, [BridgeConstantsInterface::TAG_CLIENT_CONFIGURATOR]);

            if ($this->app instanceof LumenApplication) {
                $this->app->singleton(SessionTrackingMiddleware::class);
                $this->app->middleware([SessionTrackingMiddleware::class]);
            }

            $events = $this->app->make('events');

            $this->app->singleton(SessionTrackingListener::class);
            $events->listen(RouteMatched::class, SessionTrackingListener::class);

            if (\config('easy-bugsnag.session_tracking.queue_job_count_for_sessions', false)) {
                $this->app->singleton(SessionTrackingQueueListener::class);
                $events->listen(JobProcessing::class, SessionTrackingQueueListener::class);
            }
        }
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function registerShutdownStrategy(): void
    {
        $this->app->singleton(BridgeConstantsInterface::SERVICE_SHUTDOWN_STRATEGY, ShutdownStrategy::class);

        // Make sure client is shutdown in worker
        $this->app->make('queue')
            ->looping(function (): void {
                $this->app->make(BridgeConstantsInterface::SERVICE_SHUTDOWN_STRATEGY)->shutdown();
            });
    }
}
