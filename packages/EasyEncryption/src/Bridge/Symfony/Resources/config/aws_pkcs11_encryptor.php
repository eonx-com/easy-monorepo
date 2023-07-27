<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyEncryption\AwsPkcs11Encryptor;
use EonX\EasyEncryption\Bridge\BridgeConstantsInterface;
use EonX\EasyEncryption\Interfaces\AwsPkcs11EncryptorInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(AwsPkcs11EncryptorInterface::class, AwsPkcs11Encryptor::class)
        ->arg('$userPin', param(BridgeConstantsInterface::PARAM_AWS_PKCS11_USER_PIN))
        ->arg('$hsmCaCert', param(BridgeConstantsInterface::PARAM_AWS_PKCS11_HSM_CA_CERT))
        ->arg(
            '$disableKeyAvailabilityCheck',
            param(BridgeConstantsInterface::PARAM_AWS_PKCS11_DISABLE_KEY_AVAILABILITY_CHECK)
        )
        ->arg('$hsmIpAddress', param(BridgeConstantsInterface::PARAM_AWS_PKCS11_HSM_IP_ADDRESS))
        ->arg('$cloudHsmClusterId', param(BridgeConstantsInterface::PARAM_AWS_PKCS11_CLOUD_HSM_CLUSTER_ID))
        ->arg('$awsRegion', param(BridgeConstantsInterface::PARAM_AWS_PKCS11_AWS_REGION))
        ->arg('$aad', param(BridgeConstantsInterface::PARAM_AWS_PKCS11_AAD))
        ->arg('$serverClientCertFile', param(BridgeConstantsInterface::PARAM_AWS_PKCS11_SERVER_CLIENT_CERT_FILE))
        ->arg('$serverClientKeyFile', param(BridgeConstantsInterface::PARAM_AWS_PKCS11_SERVER_CLIENT_KEY_FILE))
        ->arg('$awsCloudHsmSdkOptions', param(BridgeConstantsInterface::PARAM_AWS_PKCS11_HSM_SDK_OPTIONS))
        ->arg('$defaultKeyName', param(BridgeConstantsInterface::PARAM_DEFAULT_KEY_NAME));
};
