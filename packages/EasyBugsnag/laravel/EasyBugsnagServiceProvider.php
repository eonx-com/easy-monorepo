<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Laravel;

use Bugsnag\Client;
use EonX\EasyBugsnag\Bundle\Enum\ConfigServiceId;
use EonX\EasyBugsnag\Bundle\Enum\ConfigTag;
use EonX\EasyBugsnag\Common\Configurator\AppNameClientConfigurator;
use EonX\EasyBugsnag\Common\Configurator\AwsEcsFargateClientConfigurator;
use EonX\EasyBugsnag\Common\Configurator\BasicsClientConfigurator;
use EonX\EasyBugsnag\Common\Configurator\RuntimeVersionClientConfigurator;
use EonX\EasyBugsnag\Common\Configurator\SensitiveDataSanitizerClientConfigurator;
use EonX\EasyBugsnag\Common\Factory\ClientFactory;
use EonX\EasyBugsnag\Common\Factory\ClientFactoryInterface;
use EonX\EasyBugsnag\Common\Resolver\AppNameResolverInterface;
use EonX\EasyBugsnag\Common\Resolver\DefaultAppNameResolver;
use EonX\EasyBugsnag\Common\Strategy\ShutdownStrategy;
use EonX\EasyBugsnag\Common\Tracker\SessionTracker;
use EonX\EasyBugsnag\Laravel\Configurators\SessionTrackingClientConfigurator;
use EonX\EasyBugsnag\Laravel\Listeners\SessionTrackingListener;
use EonX\EasyBugsnag\Laravel\Listeners\SessionTrackingQueueListener;
use EonX\EasyBugsnag\Laravel\Middleware\SessionTrackingMiddleware;
use EonX\EasyBugsnag\Laravel\Resolvers\LaravelRequestResolver;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Lumen\Application as LumenApplication;
use LogicException;

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
                AppNameClientConfigurator::class,
                static fn (Container $app): AppNameClientConfigurator => new AppNameClientConfigurator(
                    $app->make(AppNameResolverInterface::class)
                )
            );
            $this->app->tag(AppNameClientConfigurator::class, [ConfigTag::ClientConfigurator->value]);
        }
    }

    private function registerAwsEcsFargate(): void
    {
        if (\config('easy-bugsnag.aws_ecs_fargate.enabled', false)) {
            $this->app->singleton(
                AwsEcsFargateClientConfigurator::class,
                static fn (): AwsEcsFargateClientConfigurator => new AwsEcsFargateClientConfigurator(
                    \config('easy-bugsnag.aws_ecs_fargate.meta_storage_filename'),
                    \config('easy-bugsnag.aws_ecs_fargate.meta_url')
                )
            );
            $this->app->tag(AwsEcsFargateClientConfigurator::class, [ConfigTag::ClientConfigurator->value]);
        }
    }

    private function registerClient(): void
    {
        // Client Factory + Client
        $this->app->singleton(
            ClientFactoryInterface::class,
            static fn (Container $app): ClientFactoryInterface => (new ClientFactory())
                ->setConfigurators($app->tagged(ConfigTag::ClientConfigurator->value))
                ->setRequestResolver($app->make(ConfigServiceId::RequestResolver->value))
                ->setShutdownStrategy($app->make(ConfigServiceId::ShutdownStrategy->value))
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
            BasicsClientConfigurator::class,
            static fn (): BasicsClientConfigurator => new BasicsClientConfigurator(
                \config('easy-bugsnag.project_root'),
                \config('easy-bugsnag.strip_path'),
                \config('easy-bugsnag.release_stage')
            )
        );
        $this->app->tag(BasicsClientConfigurator::class, [ConfigTag::ClientConfigurator->value]);

        $this->app->singleton(
            RuntimeVersionClientConfigurator::class,
            static function (Container $app): RuntimeVersionClientConfigurator {
                /** @var \Illuminate\Contracts\Foundation\Application $app */
                $version = $app->version();
                $runtime = Str::contains($version, 'Lumen') ? 'lumen' : 'laravel';

                return new RuntimeVersionClientConfigurator($runtime, $version);
            }
        );
        $this->app->tag(RuntimeVersionClientConfigurator::class, [ConfigTag::ClientConfigurator->value]);
    }

    private function registerRequestResolver(): void
    {
        // Request Resolver
        $this->app->singleton(ConfigServiceId::RequestResolver->value, LaravelRequestResolver::class);
    }

    private function registerSensitiveDataSanitizer(): void
    {
        if (\config('easy-bugsnag.sensitive_data_sanitizer.enabled', true)) {
            $this->app->singleton(
                SensitiveDataSanitizerClientConfigurator::class,
                static function (Container $app): SensitiveDataSanitizerClientConfigurator {
                    $sanitizerId = SensitiveDataSanitizerInterface::class;

                    if (\interface_exists($sanitizerId) === false || $app->has($sanitizerId) === false) {
                        throw new LogicException(
                            'To use sensitive data sanitization, the package eonx-com/easy-utils must be installed,
                            and its service provider must be registered'
                        );
                    }

                    return new SensitiveDataSanitizerClientConfigurator($app->make($sanitizerId));
                }
            );
            $this->app->tag(
                SensitiveDataSanitizerClientConfigurator::class,
                [ConfigTag::ClientConfigurator->value]
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
                ConfigServiceId::SessionTrackingCache->value,
                static fn (Container $app): Repository => $app->make('cache')
                    ->store(\config('easy-bugsnag.session_tracking.cache_store', 'file'))
            );

            $this->app->singleton(
                SessionTrackingClientConfigurator::class,
                static fn (Container $app): SessionTrackingClientConfigurator => new SessionTrackingClientConfigurator(
                    $app->make(ConfigServiceId::SessionTrackingCache->value),
                    \config('easy-bugsnag.session_tracking.cache_expires_after', 3600)
                )
            );
            $this->app->tag(SessionTrackingClientConfigurator::class, [ConfigTag::ClientConfigurator->value]);

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
        $this->app->singleton(ConfigServiceId::ShutdownStrategy->value, ShutdownStrategy::class);

        // Make sure client is shutdown in worker
        $this->app->make('queue')
            ->looping(function (): void {
                $this->app->make(ConfigServiceId::ShutdownStrategy->value)->shutdown();
            });
    }
}
