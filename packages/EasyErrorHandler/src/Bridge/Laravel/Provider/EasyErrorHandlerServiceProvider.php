<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Laravel\Provider;

use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface;
use EonX\EasyErrorHandler\Bridge\EasyWebhook\WebhookFinalFailedListener;
use EonX\EasyErrorHandler\Bridge\Laravel\Console\Commands\Lumen\AnalyzeErrorCodesCommand;
use EonX\EasyErrorHandler\Bridge\Laravel\ExceptionHandler;
use EonX\EasyErrorHandler\Bridge\Laravel\Translator;
use EonX\EasyErrorHandler\ErrorHandler;
use EonX\EasyErrorHandler\Interfaces\ErrorCodesGroupProcessorInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorCodesProviderInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseFactoryInterface;
use EonX\EasyErrorHandler\Interfaces\IgnoreExceptionsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use EonX\EasyErrorHandler\Interfaces\VerboseStrategyInterface;
use EonX\EasyErrorHandler\Processors\ErrorCodesGroupProcessor;
use EonX\EasyErrorHandler\Providers\DefaultErrorResponseBuilderProvider;
use EonX\EasyErrorHandler\Providers\ErrorCodesFromEnumProvider;
use EonX\EasyErrorHandler\Providers\ErrorCodesFromInterfaceProvider;
use EonX\EasyErrorHandler\Resolvers\DefaultIgnoreExceptionsResolver;
use EonX\EasyErrorHandler\Resolvers\ErrorDetailsResolver;
use EonX\EasyErrorHandler\Resolvers\ErrorLogLevelResolver;
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
                $app->make(LoggerInterface::class),
                $app->make(VerboseStrategyInterface::class),
                $app->make(ErrorDetailsResolverInterface::class),
                $app->make(ErrorLogLevelResolverInterface::class),
                $app->make(IgnoreExceptionsResolverInterface::class),
                $app->tagged(BridgeConstantsInterface::TAG_ERROR_RESPONSE_BUILDER_PROVIDER),
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

        $this->app->singleton(
            IgnoreExceptionsResolverInterface::class,
            static fn (): IgnoreExceptionsResolverInterface => new DefaultIgnoreExceptionsResolver(
                \config('easy-error-handler.ignored_exceptions'),
                false,
                (bool)\config('easy-error-handler.report_retryable_exception_attempts', false)
            )
        );

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
