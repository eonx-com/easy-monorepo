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
        'aws_region' => BridgeConstantsInterface::PARAM_AWS_RDS_IAM_REGION,
        'aws_username' => BridgeConstantsInterface::PARAM_AWS_RDS_IAM_USERNAME,
        'cache_expiry_in_seconds' => BridgeConstantsInterface::PARAM_AWS_RDS_IAM_CACHE_EXPIRY_IN_SECONDS,
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

        // AWS RDS IAM
        if ($config['aws_rds_iam']['enabled'] ?? false) {
            foreach (self::AWS_RDS_IAM_CONFIG as $configName => $param) {
                $container->setParameter($param, $config['aws_rds_iam'][$configName]);
            }

            $loader->load('aws_rds_iam.php');
        }
    }
}
