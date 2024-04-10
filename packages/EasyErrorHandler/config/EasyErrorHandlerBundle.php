<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Config;

use Bugsnag\Client;
use EonX\EasyErrorHandler\Interfaces\ErrorReporterProviderInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderProviderInterface;
use EonX\EasyErrorHandler\Interfaces\VerboseStrategyDriverInterface;
use EonX\EasyWebhook\Events\FinalFailedWebhookEvent;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyErrorHandlerBundle extends AbstractBundle
{
    private const DEFAULT_LOCALE = 'en';

    protected string $extensionAlias = 'easy_error_handler';

    public function __construct()
    {
        $this->path = \realpath(__DIR__) . '/..';
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container
            ->addCompilerPass(new ApiPlatformCompilerPass())
            ->addCompilerPass(new ErrorHandlerCompilerPass())
            ->addCompilerPass(new ErrorRendererCompilerPass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__ . '/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $parameters = $container->parameters();

        $parameters->set(BridgeConstantsInterface::PARAM_BUGSNAG_THRESHOLD, $config['bugsnag_threshold']);
        $parameters->set(
            BridgeConstantsInterface::PARAM_BUGSNAG_IGNORED_EXCEPTIONS,
            \count($config['bugsnag_ignored_exceptions']) > 0 ? $config['bugsnag_ignored_exceptions'] : null
        );
        $parameters->set(
            BridgeConstantsInterface::PARAM_BUGSNAG_IGNORE_VALIDATION_ERRORS,
            $config['bugsnag_ignore_validation_errors']
        );
        $parameters->set(
            BridgeConstantsInterface::PARAM_BUGSNAG_HANDLED_EXCEPTIONS,
            \count($config['bugsnag_handled_exceptions']) > 0 ? $config['bugsnag_handled_exceptions'] : null
        );

        $parameters->set(
            BridgeConstantsInterface::PARAM_TRANSFORM_VALIDATION_ERRORS,
            $config['transform_validation_errors']
        );

        $parameters->set(
            BridgeConstantsInterface::PARAM_IGNORED_EXCEPTIONS,
            \count($config['ignored_exceptions']) > 0 ? $config['ignored_exceptions'] : null
        );
        $parameters->set(
            BridgeConstantsInterface::PARAM_REPORT_RETRYABLE_EXCEPTION_ATTEMPTS,
            $config['report_retryable_exception_attempts'] ?? false
        );

        $parameters->set(
            BridgeConstantsInterface::PARAM_SKIP_REPORTED_EXCEPTIONS,
            $config['skip_reported_exceptions'] ?? false
        );

        $parameters->set(BridgeConstantsInterface::PARAM_IS_VERBOSE, $config['verbose']);

        $parameters->set(
            BridgeConstantsInterface::PARAM_LOGGER_EXCEPTION_LOG_LEVELS,
            \count($config['logger_exception_log_levels']) > 0 ? $config['logger_exception_log_levels'] : null
        );
        $parameters->set(
            BridgeConstantsInterface::PARAM_LOGGER_IGNORED_EXCEPTIONS,
            \count($config['logger_ignored_exceptions']) > 0 ? $config['logger_ignored_exceptions'] : null
        );

        $parameters->set(
            BridgeConstantsInterface::PARAM_OVERRIDE_API_PLATFORM_LISTENER,
            $config['override_api_platform_listener']
        );
        $parameters->set(BridgeConstantsInterface::PARAM_RESPONSE_KEYS, $config['response']);
        $parameters->set(BridgeConstantsInterface::PARAM_TRANSLATION_DOMAIN, $config['translation_domain']);

        $parameters->set(
            BridgeConstantsInterface::PARAM_ERROR_CODES_INTERFACE,
            $config['error_codes_interface']
        );
        $parameters->set(
            BridgeConstantsInterface::PARAM_ERROR_CODES_CATEGORY_SIZE,
            $config['error_codes_category_size']
        );
        $parameters->set(
            BridgeConstantsInterface::PARAM_TRANSLATE_INTERNAL_ERROR_MESSAGES_ENABLED,
            $config['translate_internal_error_messages']['enabled'] ?? false
        );
        $parameters->set(
            BridgeConstantsInterface::PARAM_TRANSLATE_INTERNAL_ERROR_MESSAGES_LOCALE,
            $config['translate_internal_error_messages']['locale'] ?? self::DEFAULT_LOCALE
        );

        $builder
            ->registerForAutoconfiguration(ErrorReporterProviderInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_ERROR_REPORTER_PROVIDER);

        $builder
            ->registerForAutoconfiguration(ErrorResponseBuilderProviderInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_ERROR_RESPONSE_BUILDER_PROVIDER);

        $builder
            ->registerForAutoconfiguration(VerboseStrategyDriverInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_VERBOSE_STRATEGY_DRIVER);

        $container->import(__DIR__ . '/services.php');

        if ($config['use_default_builders'] ?? true) {
            $container->import(__DIR__ . '/default_builders.php');
        }

        if ($config['override_api_platform_listener'] ?? true) {
            $container->import(__DIR__ . '/api_platform_builders.php');
        }

        if ($config['use_default_reporters'] ?? true) {
            $container->import(__DIR__ . '/default_reporters.php');
        }

        if (($config['bugsnag_enabled'] ?? true) && \class_exists(Client::class)) {
            $container->import(__DIR__ . '/bugsnag_reporter.php');
        }

        // EasyWebhook Bridge
        if (\class_exists(FinalFailedWebhookEvent::class)) {
            $container->import(__DIR__ . '/easy_webhook.php');
        }
    }
}
