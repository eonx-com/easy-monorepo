<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Laravel\Provider;

use Bugsnag\Client;
use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface as EasyBugsnagConstantsInterface;
use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Configurators\ErrorDetailsClientConfigurator;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Configurators\SeverityClientConfigurator;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Configurators\UnhandledClientConfigurator;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Interfaces\BugsnagIgnoreExceptionsResolverInterface;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Providers\BugsnagErrorReporterProvider;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Resolvers\DefaultBugsnagIgnoreExceptionsResolver;
use EonX\EasyErrorHandler\Bridge\EasyWebhook\WebhookFinalFailedListener;
use EonX\EasyErrorHandler\Bridge\Laravel\Console\Commands\Lumen\AnalyzeErrorCodesCommand;
use EonX\EasyErrorHandler\Bridge\Laravel\ExceptionHandler;
use EonX\EasyErrorHandler\Bridge\Laravel\Translator;
use EonX\EasyErrorHandler\ErrorDetailsResolver;
use EonX\EasyErrorHandler\ErrorHandler;
use EonX\EasyErrorHandler\ErrorLogLevelResolver;
use EonX\EasyErrorHandler\Interfaces\ErrorCodesGroupProcessorInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorCodesProviderInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseFactoryInterface;
use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use EonX\EasyErrorHandler\Interfaces\VerboseStrategyInterface;
use EonX\EasyErrorHandler\Processors\ErrorCodesGroupProcessor;
use EonX\EasyErrorHandler\Providers\DefaultErrorReporterProvider;
use EonX\EasyErrorHandler\Providers\DefaultErrorResponseBuilderProvider;
use EonX\EasyErrorHandler\Providers\ErrorCodesFromEnumProvider;
use EonX\EasyErrorHandler\Providers\ErrorCodesFromInterfaceProvider;
use EonX\EasyErrorHandler\Response\ErrorResponseFactory;
use EonX\EasyErrorHandler\Verbose\ChainVerboseStrategy;
use EonX\EasyWebhook\Events\FinalFailedWebhookEvent;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler as IlluminateExceptionHandlerInterface;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Psr\Log\LoggerInterface;

final class EasyErrorHandlerServiceProvider extends ServiceProvider
{
    /**
     * @var string[]
     */
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
        $this->loadTranslationsFrom(__DIR__ . '/../translations', BridgeConstantsInterface::TRANSLATION_NAMESPACE);

        $this->publishes([
            __DIR__ . '/../config/easy-error-handler.php' => \base_path('config/easy-error-handler.php'),
        ]);

        // EasyWebhook Bridge
        if (\class_exists(FinalFailedWebhookEvent::class)) {
            $this->app->make('events')
                ->listen(FinalFailedWebhookEvent::class, WebhookFinalFailedListener::class);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/easy-error-handler.php', 'easy-error-handler');

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
                \config('easy-error-handler.logger_exception_log_levels')
            )
        );

        $this->app->singleton(ErrorResponseFactoryInterface::class, ErrorResponseFactory::class);

        $this->app->singleton(
            VerboseStrategyInterface::class,
            static fn (Container $app): VerboseStrategyInterface => new ChainVerboseStrategy(
                $app->tagged(BridgeConstantsInterface::TAG_VERBOSE_STRATEGY_DRIVER),
                (bool)\config('easy-error-handler.use_extended_response', false)
            )
        );

        $this->app->singleton(
            ErrorHandlerInterface::class,
            static fn (Container $app): ErrorHandlerInterface => new ErrorHandler(
                $app->make(ErrorResponseFactoryInterface::class),
                $app->tagged(BridgeConstantsInterface::TAG_ERROR_RESPONSE_BUILDER_PROVIDER),
                $app->tagged(BridgeConstantsInterface::TAG_ERROR_REPORTER_PROVIDER),
                $app->make(VerboseStrategyInterface::class),
                \config('easy-error-handler.ignored_exceptions')
            )
        );

        $this->app->singleton(
            IlluminateExceptionHandlerInterface::class,
            static fn (Container $app): IlluminateExceptionHandlerInterface => new ExceptionHandler(
                $app->make(ErrorHandlerInterface::class),
                $app->make(TranslatorInterface::class)
            )
        );

        $this->app->singleton(
            TranslatorInterface::class,
            static fn (Container $app): TranslatorInterface => new Translator($app->make('translator'))
        );

        if ((bool)\config('easy-error-handler.use_default_builders', true)) {
            $this->app->singleton(
                DefaultErrorResponseBuilderProvider::class,
                static fn (
                    Container $app,
                ): DefaultErrorResponseBuilderProvider => new DefaultErrorResponseBuilderProvider(
                    $app->make(ErrorDetailsResolverInterface::class),
                    $app->make(TranslatorInterface::class),
                    \config('easy-error-handler.response')
                )
            );
            $this->app->tag(
                DefaultErrorResponseBuilderProvider::class,
                [BridgeConstantsInterface::TAG_ERROR_RESPONSE_BUILDER_PROVIDER]
            );
        }

        if ((bool)\config('easy-error-handler.use_default_reporters', true)) {
            $this->app->singleton(
                DefaultErrorReporterProvider::class,
                static fn (Container $app): DefaultErrorReporterProvider => new DefaultErrorReporterProvider(
                    $app->make(ErrorDetailsResolverInterface::class),
                    $app->make(ErrorLogLevelResolverInterface::class),
                    $app->make(LoggerInterface::class),
                    \config('easy-error-handler.logger_ignored_exceptions')
                )
            );
            $this->app->tag(
                DefaultErrorReporterProvider::class,
                [BridgeConstantsInterface::TAG_ERROR_REPORTER_PROVIDER]
            );
        }

        $this->app->singleton(
            BugsnagIgnoreExceptionsResolverInterface::class,
            static fn (): BugsnagIgnoreExceptionsResolverInterface => new DefaultBugsnagIgnoreExceptionsResolver(
                \config('easy-error-handler.bugsnag_ignored_exceptions'),
                false
            )
        );

        if ((bool)\config('easy-error-handler.bugsnag_enabled', true) && \class_exists(Client::class)) {
            $this->app->singleton(
                BugsnagErrorReporterProvider::class,
                static fn (Container $app): BugsnagErrorReporterProvider => new BugsnagErrorReporterProvider(
                    $app->make(Client::class),
                    $app->make(BugsnagIgnoreExceptionsResolverInterface::class),
                    $app->make(ErrorLogLevelResolverInterface::class),
                    \config('easy-error-handler.bugsnag_threshold')
                )
            );
            $this->app->tag(
                BugsnagErrorReporterProvider::class,
                [BridgeConstantsInterface::TAG_ERROR_REPORTER_PROVIDER]
            );

            foreach (self::BUGSNAG_CONFIGURATORS as $configurator) {
                $this->app->singleton($configurator);
                $this->app->tag($configurator, [EasyBugsnagConstantsInterface::TAG_CLIENT_CONFIGURATOR]);
            }

            $this->app->singleton(
                UnhandledClientConfigurator::class,
                static fn (): UnhandledClientConfigurator => new UnhandledClientConfigurator(
                    \config('easy-error-handler.bugsnag_handled_exceptions')
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
