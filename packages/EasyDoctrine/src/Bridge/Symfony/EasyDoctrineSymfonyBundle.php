<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony;

use EonX\EasyDoctrine\Bridge\BridgeConstantsInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyDoctrineSymfonyBundle extends AbstractBundle
{
    private const AWS_RDS_IAM_CONFIG = [
        'auth_token_lifetime_in_minutes' => BridgeConstantsInterface::PARAM_AWS_RDS_IAM_AUTH_TOKEN_LIFETIME_IN_MINUTES,
        'aws_region' => BridgeConstantsInterface::PARAM_AWS_RDS_IAM_AWS_REGION,
        'aws_username' => BridgeConstantsInterface::PARAM_AWS_RDS_IAM_AWS_USERNAME,
    ];

    private const AWS_RDS_SSL_CONFIG = [
        'ca_path' => BridgeConstantsInterface::PARAM_AWS_RDS_SSL_CA_PATH,
        'mode' => BridgeConstantsInterface::PARAM_AWS_RDS_SSL_MODE,
    ];

    protected string $extensionAlias = 'easy_doctrine';

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__ . '/Resources/config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container
            ->parameters()
            ->set(
                BridgeConstantsInterface::PARAM_DEFERRED_DISPATCHER_ENTITIES,
                $config['deferred_dispatcher_entities']
            );

        $container->import(__DIR__ . '/Resources/config/services.php');

        /** @var array<string, string> $bundles */
        $bundles = $builder->getParameter('kernel.bundles');

        if ($config['easy_error_handler_enabled'] && isset($bundles['EasyErrorHandlerSymfonyBundle']) === true) {
            $container->import(__DIR__ . '/Resources/config/easy-error-handler-listener.php');
        }

        $awsRdsSslEnabled = $this->loadAwsRdsSsl($container, $config);
        $awsRdsIamEnabled = $this->loadAwsRdsIam($container, $config);

        if ($awsRdsSslEnabled || $awsRdsIamEnabled) {
            if ($builder->hasParameter(BridgeConstantsInterface::PARAM_AWS_RDS_SSL_MODE) === false) {
                $container
                    ->parameters()
                    ->set(BridgeConstantsInterface::PARAM_AWS_RDS_SSL_MODE, null);
            }

            $container->import(__DIR__ . '/Resources/config/aws_rds.php');
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
            ->set(BridgeConstantsInterface::PARAM_AWS_RDS_IAM_AWS_USERNAME, null);

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
                    ->set($param, $value);
            }

            $container->import(__DIR__ . '/Resources/config/aws_rds_iam.php');
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
                    ->set($param, $value);
            }

            $container->import(__DIR__ . '/Resources/config/aws_rds_ssl.php');
        }

        return $awsRdsSslEnabled;
    }
}
