<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyEncryption\AwsCloudHsm\Builder\AwsCloudHsmSdkOptionsBuilder;
use EonX\EasyEncryption\AwsCloudHsm\Configurator\AwsCloudHsmSdkConfigurator;
use EonX\EasyEncryption\AwsCloudHsm\Encryptor\AwsCloudHsmEncryptor;
use EonX\EasyEncryption\AwsCloudHsm\Encryptor\AwsCloudHsmEncryptorInterface;
use EonX\EasyEncryption\AwsCloudHsm\HashCalculator\AwsCloudHsmHashCalculator;
use EonX\EasyEncryption\Bundle\Enum\BundleParam;
use EonX\EasyEncryption\Bundle\Enum\ConfigParam;
use EonX\EasyEncryption\Common\Encryptor\EncryptorInterface;
use EonX\EasyEncryption\Encryptable\HashCalculator\HashCalculatorInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(AwsCloudHsmSdkOptionsBuilder::class)
        ->arg('$caCertFile', param(ConfigParam::AwsCloudHsmCaCertFile->value))
        ->arg(
            '$disableKeyAvailabilityCheck',
            param(ConfigParam::AwsCloudHsmDisableKeyAvailabilityCheck->value)
        )
        ->arg('$ipAddress', param(ConfigParam::AwsCloudHsmIpAddress->value))
        ->arg('$clusterId', param(ConfigParam::AwsCloudHsmClusterId->value))
        ->arg('$region', param(ConfigParam::AwsCloudHsmRegion->value))
        ->arg('$serverClientCertFile', param(ConfigParam::AwsCloudHsmServerClientCertFile->value))
        ->arg('$serverClientKeyFile', param(ConfigParam::AwsCloudHsmServerClientKeyFile->value))
        ->arg('$sdkOptions', param(ConfigParam::AwsCloudHsmSdkOptions->value));

    $services
        ->set(AwsCloudHsmSdkConfigurator::class)
        ->arg('$roleArn', param(ConfigParam::AwsCloudHsmRoleArn->value))
        ->arg('$useConfigureTool', param(ConfigParam::AwsCloudHsmUseConfigureTool->value))
        ->arg('$clusterType', param(ConfigParam::AwsCloudHsmClusterType->value))
        ->arg('$serverPort', param(ConfigParam::AwsCloudHsmServerPort->value));

    $services
        ->set(AwsCloudHsmEncryptorInterface::class, AwsCloudHsmEncryptor::class)
        ->arg('$userPin', param(ConfigParam::AwsCloudHsmUserPin->value))
        ->arg('$aad', param(ConfigParam::AwsCloudHsmAad->value))
        ->arg('$defaultKeyName', param(ConfigParam::DefaultKeyName->value))
        ->tag('monolog.logger', ['channel' => BundleParam::LogChannel->value]);

    $services
        ->alias(EncryptorInterface::class, AwsCloudHsmEncryptorInterface::class);

    $services->set(HashCalculatorInterface::class, AwsCloudHsmHashCalculator::class)
        ->arg('$signKeyName', param(ConfigParam::AwsCloudHsmSignKeyName->value));
};
