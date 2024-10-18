<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bundle;

use EonX\EasyErrorHandler\Bugsnag\Ignorer\BugsnagExceptionIgnorerInterface;
use EonX\EasyErrorHandler\Bundle\CompilerPass\ErrorRendererCompilerPass;
use EonX\EasyErrorHandler\Bundle\CompilerPass\RegisterTraceableErrorHandlerCompilerPass;
use EonX\EasyErrorHandler\Bundle\Enum\ConfigParam;
use EonX\EasyErrorHandler\Bundle\Enum\ConfigTag;
use EonX\EasyErrorHandler\Common\Driver\VerboseStrategyDriverInterface;
use EonX\EasyErrorHandler\Common\Provider\ErrorReporterProviderInterface;
use EonX\EasyErrorHandler\Common\Provider\ErrorResponseBuilderProviderInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyErrorHandlerBundle extends AbstractBundle
{
    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new RegisterTraceableErrorHandlerCompilerPass())
            ->addCompilerPass(new ErrorRendererCompilerPass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder
            ->registerForAutoconfiguration(ErrorReporterProviderInterface::class)
            ->addTag(ConfigTag::ErrorReporterProvider->value);

        $builder
            ->registerForAutoconfiguration(ErrorResponseBuilderProviderInterface::class)
            ->addTag(ConfigTag::ErrorResponseBuilderProvider->value);

        $builder
            ->registerForAutoconfiguration(VerboseStrategyDriverInterface::class)
            ->addTag(ConfigTag::VerboseStrategyDriver->value);

        $container
            ->parameters()
            ->set(ConfigParam::IgnoredExceptions->value, $config['ignored_exceptions'])
            ->set(ConfigParam::ReportRetryableExceptionAttempts->value, $config['report_retryable_exception_attempts'])
            ->set(ConfigParam::SkipReportedExceptions->value, $config['skip_reported_exceptions'])
            ->set(ConfigParam::IsVerbose->value, $config['verbose'])
            ->set(ConfigParam::LoggerExceptionLogLevels->value, $config['logger']['exception_log_levels'])
            ->set(ConfigParam::LoggerIgnoredExceptions->value, $config['logger']['ignored_exceptions'])
            ->set(ConfigParam::ResponseKeys->value, $config['response'])
            ->set(ConfigParam::TranslationDomain->value, $config['translation_domain'])
            ->set(ConfigParam::ErrorCodesInterface->value, $config['error_codes_interface'])
            ->set(ConfigParam::ErrorCodesCategorySize->value, $config['error_codes_category_size'])
            ->set(
                ConfigParam::TranslateInternalErrorMessagesEnabled->value,
                $config['translate_internal_error_messages']['enabled']
            )
            ->set(
                ConfigParam::TranslateInternalErrorMessagesLocale->value,
                $config['translate_internal_error_messages']['locale']
            );

        $container->import('config/services.php');

        if ($config['use_default_reporters']) {
            $container->import('config/default_reporters.php');
        }

        if ($this->isBundleEnabled('EasyWebhookBundle', $builder)) {
            $container->import('config/easy_webhook.php');
        }

        $this->registerBugsnagConfiguration($config, $container, $builder);
        $this->registerDefaultBuildersConfiguration($config, $container, $builder);
    }

    private function isBundleEnabled(string $bundleName, ContainerBuilder $builder): bool
    {
        /** @var array $bundles */
        $bundles = $builder->getParameter('kernel.bundles');

        return isset($bundles[$bundleName]);
    }

    private function registerBugsnagConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $config = $config['bugsnag'];

        if ($config['enabled'] === false) {
            return;
        }

        if ($this->isBundleEnabled('EasyBugsnagBundle', $builder) === false) {
            throw new LogicException('Install EasyBugsnagBundle to use Bugsnag integration.');
        }

        $builder->registerForAutoconfiguration(BugsnagExceptionIgnorerInterface::class)
            ->addTag(ConfigTag::BugsnagExceptionIgnorer->value);

        $container
            ->parameters()
            ->set(ConfigParam::BugsnagThreshold->value, $config['threshold'])
            ->set(ConfigParam::BugsnagIgnoredExceptions->value, $config['ignored_exceptions'])
            ->set(ConfigParam::BugsnagHandledExceptions->value, $config['handled_exceptions']);

        $container->import('config/bugsnag.php');
    }

    private function registerDefaultBuildersConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        if ($config['use_default_builders'] === false) {
            return;
        }

        $container
            ->parameters()
            ->set(ConfigParam::ExceptionToMessage->value, $config['exception_to_message'])
            ->set(ConfigParam::ExceptionToStatusCode->value, $config['exception_to_status_code'])
            ->set(ConfigParam::ExceptionToCode->value, $config['exception_to_code']);

        $container->import('config/default_builders.php');
    }
}
