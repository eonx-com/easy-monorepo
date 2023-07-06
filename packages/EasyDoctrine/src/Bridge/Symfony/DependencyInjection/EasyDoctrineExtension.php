<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony\DependencyInjection;

use EonX\EasyDoctrine\Bridge\BridgeConstantsInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyDoctrineExtension extends Extension
{
    private const AWS_RDS_IAM_CONFIG = [
        'aws_region' => BridgeConstantsInterface::PARAM_AWS_RDS_IAM_AWS_REGION,
        'aws_username' => BridgeConstantsInterface::PARAM_AWS_RDS_IAM_AWS_USERNAME,
        'auth_token_lifetime_in_minutes' => BridgeConstantsInterface::PARAM_AWS_RDS_IAM_AUTH_TOKEN_LIFETIME_IN_MINUTES,
    ];

    private const AWS_RDS_SSL_CONFIG = [
        'ca_path' => BridgeConstantsInterface::PARAM_AWS_RDS_SSL_CA_PATH,
        'mode' => BridgeConstantsInterface::PARAM_AWS_RDS_SSL_MODE,
    ];

    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter(
            BridgeConstantsInterface::PARAM_DEFERRED_DISPATCHER_ENTITIES,
            $config['deferred_dispatcher_entities']
        );

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        /** @var array<string, string> $bundles */
        $bundles = $container->getParameter('kernel.bundles');

        if ($config['easy_error_handler_enabled'] && isset($bundles['EasyErrorHandlerSymfonyBundle']) === true) {
            $loader->load('easy-error-handler-listener.php');
        }

        $awsRdsSslEnabled = $this->loadAwsRdsSsl($container, $loader, $config);
        $awsRdsIamEnabled = $this->loadAwsRdsIam($container, $loader, $config);

        if ($awsRdsSslEnabled || $awsRdsIamEnabled) {
            if ($container->hasParameter(BridgeConstantsInterface::PARAM_AWS_RDS_SSL_MODE) === false) {
                $container->setParameter(BridgeConstantsInterface::PARAM_AWS_RDS_SSL_MODE, null);
            }

            $loader->load('aws_rds.php');
        }
    }

    /**
     * @param mixed[] $config
     *
     * @throws \Exception
     */
    private function loadAwsRdsIam(ContainerBuilder $container, PhpFileLoader $loader, array $config): bool
    {
        $awsRdsIamEnabled = $config['aws_rds']['iam']['enabled']
            ?? $config['aws_rds_iam']['enabled']
            ?? false;

        // Always set AWS Username parameter so AwsRdsConnectionParamsResolver gets a default value
        $container->setParameter(BridgeConstantsInterface::PARAM_AWS_RDS_IAM_AWS_USERNAME, null);

        if ($awsRdsIamEnabled) {
            foreach (self::AWS_RDS_IAM_CONFIG as $configName => $param) {
                $value = $config['aws_rds']['iam'][$configName]
                    ?? $config['aws_rds_iam'][$configName]
                    ?? null;

                if ($configName === 'auth_token_lifetime_in_minutes') {
                    $value = $value ?? (($config['aws_rds_iam']['cache_expiry_in_seconds'] ?? 15) / 60);
                }

                $container->setParameter($param, $value);
            }

            $loader->load('aws_rds_iam.php');
        }

        return $awsRdsIamEnabled;
    }

    /**
     * @param mixed[] $config
     *
     * @throws \Exception
     */
    private function loadAwsRdsSsl(ContainerBuilder $container, PhpFileLoader $loader, array $config): bool
    {
        $awsRdsSslEnabled = $config['aws_rds']['ssl']['enabled']
            ?? $config['aws_rds_iam']['ssl_enabled']
            ?? false;

        if ($awsRdsSslEnabled) {
            foreach (self::AWS_RDS_SSL_CONFIG as $configName => $param) {
                $value = $config['aws_rds']['ssl'][$configName] ?? null;

                if ($configName === 'ca_path') {
                    $value = $value ?? $config['aws_rds_iam']['ssl_cert_dir'] . '/rds-combined-ca-bundle.pem';
                }
                if ($configName === 'mode') {
                    $value = $value ?? $config['aws_rds_iam']['ssl_mode'] ?? null;
                }

                $container->setParameter($param, $value);
            }

            $loader->load('aws_rds_ssl.php');
        }

        return $awsRdsSslEnabled;
    }
}
