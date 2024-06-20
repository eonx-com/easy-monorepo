<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Bundle;

use EonX\EasyDoctrine\Bundle\Enum\ConfigParam;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyDoctrineBundle extends AbstractBundle
{
    private const AWS_RDS_IAM_CONFIG = [
        'auth_token_lifetime_in_minutes' => ConfigParam::AwsRdsIamAuthTokenLifetimeInMinutes,
        'aws_region' => ConfigParam::AwsRdsIamAwsRegion,
        'aws_username' => ConfigParam::AwsRdsIamAwsUsername,
    ];

    private const AWS_RDS_SSL_CONFIG = [
        'ca_path' => ConfigParam::AwsRdsSslCaPath,
        'mode' => ConfigParam::AwsRdsSslMode,
    ];

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container
            ->parameters()
            ->set(
                ConfigParam::DeferredDispatcherEntities->value,
                $config['deferred_dispatcher_entities']
            );

        $container->import('config/services.php');

        /** @var array<string, string> $bundles */
        $bundles = $builder->getParameter('kernel.bundles');

        if ($config['easy_error_handler_enabled'] && isset($bundles['EasyErrorHandlerSymfonyBundle']) === true) {
            $container->import('config/easy-error-handler-listener.php');
        }

        $awsRdsSslEnabled = $this->loadAwsRdsSsl($container, $config);
        $awsRdsIamEnabled = $this->loadAwsRdsIam($container, $config);

        if ($awsRdsSslEnabled || $awsRdsIamEnabled) {
            if ($builder->hasParameter(ConfigParam::AwsRdsSslMode->value) === false) {
                $container
                    ->parameters()
                    ->set(ConfigParam::AwsRdsSslMode->value, null);
            }

            $container->import('config/aws_rds.php');
        }
    }

    /**
     * @throws \Exception
     */
    private function loadAwsRdsIam(ContainerConfigurator $container, array $config): bool
    {
        $awsRdsIamEnabled = $config['aws_rds']['iam']['enabled']
            ?? $config['aws_rds_iam']['enabled']
            ?? false;

        // Always set AWS Username parameter so AwsRdsConnectionParamsResolver gets a default value
        $container
            ->parameters()
            ->set(ConfigParam::AwsRdsIamAwsUsername->value, null);

        if ($awsRdsIamEnabled) {
            foreach (self::AWS_RDS_IAM_CONFIG as $configName => $param) {
                $value = $config['aws_rds']['iam'][$configName]
                    ?? $config['aws_rds_iam'][$configName]
                    ?? null;

                if ($configName === 'auth_token_lifetime_in_minutes') {
                    $value ??= (int)$config['aws_rds_iam']['cache_expiry_in_seconds'] / 60;
                }

                $container
                    ->parameters()
                    ->set($param->value, $value);
            }

            $container->import('config/aws_rds_iam.php');
        }

        return $awsRdsIamEnabled;
    }

    /**
     * @throws \Exception
     */
    private function loadAwsRdsSsl(ContainerConfigurator $container, array $config): bool
    {
        $awsRdsSslEnabled = $config['aws_rds']['ssl']['enabled']
            ?? $config['aws_rds_iam']['ssl_enabled']
            ?? false;

        if ($awsRdsSslEnabled) {
            foreach (self::AWS_RDS_SSL_CONFIG as $configName => $param) {
                $value = $config['aws_rds']['ssl'][$configName] ?? null;

                if ($configName === 'ca_path') {
                    $value ??= $config['aws_rds_iam']['ssl_cert_dir'] . '/rds-combined-ca-bundle.pem';
                }

                if ($configName === 'mode') {
                    $value ??= $config['aws_rds_iam']['ssl_mode'];
                }

                $container
                    ->parameters()
                    ->set($param->value, $value);
            }

            $container->import('config/aws_rds_ssl.php');
        }

        return $awsRdsSslEnabled;
    }
}
