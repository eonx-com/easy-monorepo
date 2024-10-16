<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Laravel;

use Bugsnag\Client;
use EonX\EasyBugsnag\Bundle\Enum\ConfigTag as EasyBugsnagConfigTag;
use EonX\EasyErrorHandler\Bugsnag\Configurator\ErrorDetailsClientConfigurator;
use EonX\EasyErrorHandler\Bugsnag\Configurator\SeverityClientConfigurator;
use EonX\EasyErrorHandler\Bugsnag\Configurator\UnhandledClientConfigurator;
use EonX\EasyErrorHandler\Bugsnag\Ignorer\BugsnagExceptionIgnorerInterface;
use EonX\EasyErrorHandler\Bugsnag\Ignorer\DefaultBugsnagExceptionIgnorer;
use EonX\EasyErrorHandler\Bugsnag\Provider\BugsnagErrorReporterProvider;
use EonX\EasyErrorHandler\Bundle\Enum\ConfigTag;
use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandler;
use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Common\Factory\ErrorResponseFactory;
use EonX\EasyErrorHandler\Common\Factory\ErrorResponseFactoryInterface;
use EonX\EasyErrorHandler\Common\Provider\DefaultErrorReporterProvider;
use EonX\EasyErrorHandler\Common\Provider\DefaultErrorResponseBuilderProvider;
use EonX\EasyErrorHandler\Common\Resolver\ErrorDetailsResolver;
use EonX\EasyErrorHandler\Common\Resolver\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Common\Resolver\ErrorLogLevelResolver;
use EonX\EasyErrorHandler\Common\Resolver\ErrorLogLevelResolverInterface;
use EonX\EasyErrorHandler\Common\Strategy\ChainVerboseStrategy;
use EonX\EasyErrorHandler\Common\Strategy\VerboseStrategyInterface;
use EonX\EasyErrorHandler\Common\Translator\TranslatorInterface;
use EonX\EasyErrorHandler\EasyWebhook\Listener\FinalFailedWebhookListener;
use EonX\EasyErrorHandler\ErrorCodes\Processor\ErrorCodesGroupProcessor;
use EonX\EasyErrorHandler\ErrorCodes\Processor\ErrorCodesGroupProcessorInterface;
use EonX\EasyErrorHandler\ErrorCodes\Provider\ErrorCodesFromEnumProvider;
use EonX\EasyErrorHandler\ErrorCodes\Provider\ErrorCodesFromInterfaceProvider;
use EonX\EasyErrorHandler\ErrorCodes\Provider\ErrorCodesProviderInterface;
use EonX\EasyErrorHandler\Laravel\Commands\AnalyzeErrorCodesCommand;
use EonX\EasyErrorHandler\Laravel\Enums\TranslationParam;
use EonX\EasyErrorHandler\Laravel\ExceptionHandlers\LaravelExceptionHandler;
use EonX\EasyErrorHandler\Laravel\Translators\LaravelTranslator;
use EonX\EasyWebhook\Common\Event\FinalFailedWebhookEvent;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler as IlluminateExceptionHandlerInterface;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Psr\Log\LoggerInterface;

final class EasyErrorHandlerServiceProvider extends ServiceProvider
{
    private const BUGSNAG_CONFIGURATORS = [
        ErrorDetailsClientConfigurator::class,
        SeverityClientConfigurator::class,
    ];

    private const DEFAULT_LOCALE = 'en';

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/translations', TranslationParam::Namespace->value);

        $this->publishes([
            __DIR__ . '/config/easy-error-handler.php' => \base_path('config/easy-error-handler.php'),
        ]);

        // EasyWebhook integration
        if (\class_exists(FinalFailedWebhookEvent::class)) {
            $this->app->make('events')
                ->listen(FinalFailedWebhookEvent::class, FinalFailedWebhookListener::class);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-error-handler.php', 'easy-error-handler');

        $this->app->singleton(
            ErrorDetailsResolverInterface::class,
            static fn (Container $app): ErrorDetailsResolverInterface => new ErrorDetailsResolver(
                $app->make(LoggerInterface::class),
                $app->make(TranslatorInterface::class),
                (bool)\config('easy-error-handler.translate_internal_error_messages.enabled', false),
                (string)\config('easy-error-handler.translate_internal_error_messages.locale', self::DEFAULT_LOCALE)
            )
        );

        $this->app->singleton(
            ErrorLogLevelResolverInterface::class,
            static fn (): ErrorLogLevelResolverInterface => new ErrorLogLevelResolver(
                \config('easy-error-handler.logger.exception_log_levels')
            )
        );

        $this->app->singleton(ErrorResponseFactoryInterface::class, ErrorResponseFactory::class);

        $this->app->singleton(
            VerboseStrategyInterface::class,
            static fn (Container $app): VerboseStrategyInterface => new ChainVerboseStrategy(
                $app->tagged(ConfigTag::VerboseStrategyDriver->value),
                (bool)\config('easy-error-handler.use_extended_response', false)
            )
        );

        $this->app->singleton(
            ErrorHandlerInterface::class,
            static fn (Container $app): ErrorHandlerInterface => new ErrorHandler(
                $app->make(ErrorResponseFactoryInterface::class),
                $app->tagged(ConfigTag::ErrorResponseBuilderProvider->value),
                $app->tagged(ConfigTag::ErrorReporterProvider->value),
                $app->make(VerboseStrategyInterface::class),
                \config('easy-error-handler.ignored_exceptions'),
                (bool)\config('easy-error-handler.report_retryable_exception_attempts', false),
                (bool)\config('easy-error-handler.skip_reported_exceptions', false)
            )
        );

        $this->app->singleton(
            IlluminateExceptionHandlerInterface::class,
            static fn (Container $app): IlluminateExceptionHandlerInterface => new LaravelExceptionHandler(
                $app->make(ErrorHandlerInterface::class),
                $app->make(TranslatorInterface::class)
            )
        );

        $this->app->singleton(
            TranslatorInterface::class,
            static fn (Container $app): TranslatorInterface => new LaravelTranslator($app->make('translator'))
        );

        if ((bool)\config('easy-error-handler.use_default_builders', true)) {
            $this->app->singleton(
                DefaultErrorResponseBuilderProvider::class,
                static fn (
                    Container $app,
                ): DefaultErrorResponseBuilderProvider => new DefaultErrorResponseBuilderProvider(
                    $app->make(ErrorDetailsResolverInterface::class),
                    $app->make(TranslatorInterface::class),
                    \config('easy-error-handler.response'),
                    \config('easy-error-handler.exception_to_message'),
                    \config('easy-error-handler.exception_to_code')
                )
            );
            $this->app->tag(
                DefaultErrorResponseBuilderProvider::class,
                [ConfigTag::ErrorResponseBuilderProvider->value]
            );
        }

        if ((bool)\config('easy-error-handler.use_default_reporters', true)) {
            $this->app->singleton(
                DefaultErrorReporterProvider::class,
                static fn (Container $app): DefaultErrorReporterProvider => new DefaultErrorReporterProvider(
                    $app->make(ErrorDetailsResolverInterface::class),
                    $app->make(ErrorLogLevelResolverInterface::class),
                    $app->make(LoggerInterface::class),
                    \config('easy-error-handler.logger.ignored_exceptions')
                )
            );
            $this->app->tag(
                DefaultErrorReporterProvider::class,
                [ConfigTag::ErrorReporterProvider->value]
            );
        }

        $this->app->singleton(
            BugsnagExceptionIgnorerInterface::class,
            static fn (): BugsnagExceptionIgnorerInterface => new DefaultBugsnagExceptionIgnorer(
                \config('easy-error-handler.bugsnag.ignored_exceptions')
            )
        );

        if ((bool)\config('easy-error-handler.bugsnag.enabled', true) && \class_exists(Client::class)) {
            $this->app->tag(
                BugsnagExceptionIgnorerInterface::class,
                [ConfigTag::BugsnagExceptionIgnorer->value]
            );

            $this->app->singleton(
                BugsnagErrorReporterProvider::class,
                static fn (Container $app): BugsnagErrorReporterProvider => new BugsnagErrorReporterProvider(
                    $app->make(Client::class),
                    $app->tagged(ConfigTag::BugsnagExceptionIgnorer->value),
                    $app->make(ErrorLogLevelResolverInterface::class),
                    \config('easy-error-handler.bugsnag.threshold')
                )
            );

            $this->app->tag(
                BugsnagErrorReporterProvider::class,
                [ConfigTag::ErrorReporterProvider->value]
            );

            foreach (self::BUGSNAG_CONFIGURATORS as $configurator) {
                $this->app->singleton($configurator);
                $this->app->tag($configurator, [EasyBugsnagConfigTag::ClientConfigurator->value]);
            }

            $this->app->singleton(
                UnhandledClientConfigurator::class,
                static fn (): UnhandledClientConfigurator => new UnhandledClientConfigurator(
                    \config('easy-error-handler.bugsnag.handled_exceptions')
                )
            );
        }

        $this->app->singleton(
            ErrorCodesFromInterfaceProvider::class,
            static fn (): ErrorCodesProviderInterface => new ErrorCodesFromInterfaceProvider(
                \config('easy-error-handler.error_codes_interface')
            )
        );

        $this->app->singleton(
            ErrorCodesFromEnumProvider::class,
            static fn (Application $app): ErrorCodesProviderInterface => new ErrorCodesFromEnumProvider(
                $app->basePath('app')
            )
        );

        $this->app->singleton(
            ErrorCodesGroupProcessorInterface::class,
            static fn (Container $app): ErrorCodesGroupProcessorInterface => new ErrorCodesGroupProcessor(
                \config('easy-error-handler.error_codes_category_size'),
                [
                    $app->make(ErrorCodesFromInterfaceProvider::class),
                    $app->make(ErrorCodesFromEnumProvider::class),
                ],
            )
        );

        $this->registerCommands();
    }

    private function registerCommands(): void
    {
        $this->commands([AnalyzeErrorCodesCommand::class]);
    }
}
