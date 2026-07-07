<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Bundle;

use EonX\EasyDoctrine\Bundle\CompilerPass\MigrationsFactoryCompilerPass;
use EonX\EasyDoctrine\Bundle\CompilerPass\WithEventsEntityManagerCompilerPass;
use EonX\EasyDoctrine\Bundle\Enum\BundleParam;
use EonX\EasyDoctrine\Bundle\Enum\ConfigParam;
use EonX\EasyDoctrine\Bundle\Enum\ConfigServiceId;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyDoctrineBundle extends AbstractBundle
{
    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new MigrationsFactoryCompilerPass())
            ->addCompilerPass(new WithEventsEntityManagerCompilerPass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('config/services.php');

        $this->registerAwsRdsConfiguration($config, $container, $builder);
        $this->registerDeferredDispatcherConfiguration($config, $container);
        $this->registerEasyErrorHandlerConfiguration($config, $container);

        $container
            ->parameters()
            ->set(ConfigParam::EntityManagerLazy->value, $config['entity_manager']['lazy']);
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if ($this->isBundleEnabled('MonologBundle', $builder) === false) {
            return;
        }

        $builder->prependExtensionConfig('monolog', [
            'channels' => [
                BundleParam::LogChannel->value,
            ],
        ]);
    }

    private function isBundleEnabled(string $bundleName, ContainerBuilder $builder): bool
    {
        /** @var array $bundles */
        $bundles = $builder->getParameter('kernel.bundles');

        return isset($bundles[$bundleName]);
    }

    private function registerAwsRdsConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        if ($config['aws_rds']['iam']['enabled'] || $config['aws_rds']['ssl']['enabled']) {
            $container->import('config/aws_rds.php');
        }

        $this->registerAwsRdsIamConfiguration($config, $container, $builder);
        $this->registerAwsRdsSslConfiguration($config, $container, $builder);
    }

    private function registerAwsRdsIamConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $config = $config['aws_rds']['iam'];

        if ($config['enabled'] === false) {
            return;
        }

        $container
            ->services()
            ->alias(ConfigServiceId::AwsRdsIamLogger->value, $this->resolveLoggerId($config['logger'], $builder));

        $container
            ->parameters()
            ->set(ConfigParam::AwsRdsIamAssumeRoleArn->value, $config['assume_role_arn'])
            ->set(ConfigParam::AwsRdsIamAssumeRoleDurationSeconds->value, $config['assume_role_duration_seconds'])
            ->set(ConfigParam::AwsRdsIamAssumeRoleRegion->value, $config['assume_role_region'])
            ->set(ConfigParam::AwsRdsIamAssumeRoleSessionName->value, $config['assume_role_session_name'])
            ->set(ConfigParam::AwsRdsIamAuthTokenLifetimeInMinutes->value, $config['auth_token_lifetime_in_minutes'])
            ->set(ConfigParam::AwsRdsIamAwsRegion->value, $config['aws_region'])
            ->set(ConfigParam::AwsRdsIamAwsUsername->value, $config['aws_username']);

        $container->import('config/aws_rds_iam.php');
    }

    private function registerAwsRdsSslConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $config = $config['aws_rds']['ssl'];

        if ($config['enabled'] === false) {
            return;
        }

        $container
            ->services()
            ->alias(ConfigServiceId::AwsRdsSslLogger->value, $this->resolveLoggerId($config['logger'], $builder));

        $container
            ->parameters()
            ->set(ConfigParam::AwsRdsSslCaPath->value, $config['ca_path'])
            ->set(ConfigParam::AwsRdsSslMode->value, $config['mode']);

        $container->import('config/aws_rds_ssl.php');
    }

    private function registerDeferredDispatcherConfiguration(
        array $config,
        ContainerConfigurator $container,
    ): void {
        if (\count($config['deferred_dispatcher_entities']) === 0) {
            return;
        }

        $container
            ->parameters()
            ->set(ConfigParam::DeferredDispatcherEntities->value, $config['deferred_dispatcher_entities']);

        $container->import('config/deferred_dispatcher.php');
    }

    private function registerEasyErrorHandlerConfiguration(
        array $config,
        ContainerConfigurator $container,
    ): void {
        if ($config['easy_error_handler']['enabled'] === false) {
            return;
        }

        $container->import('config/easy_error_handler_listener.php');
    }

    /**
     * Resolves the logger service id for the AWS RDS providers. When the user has not configured an explicit logger,
     * the dedicated "easy_doctrine" channel logger is used if symfony/monolog-bundle is enabled (see
     * prependExtension); otherwise it falls back to the default PSR logger.
     */
    private function resolveLoggerId(?string $configuredLogger, ContainerBuilder $builder): string
    {
        if ($configuredLogger !== null) {
            return $configuredLogger;
        }

        if ($this->isBundleEnabled('MonologBundle', $builder)) {
            return \sprintf('monolog.logger.%s', BundleParam::LogChannel->value);
        }

        return LoggerInterface::class;
    }
}
