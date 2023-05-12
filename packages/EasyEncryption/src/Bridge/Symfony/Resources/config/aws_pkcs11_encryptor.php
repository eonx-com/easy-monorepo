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
        ->arg('$userPin', '%' . BridgeConstantsInterface::PARAM_AWS_PKCS11_USER_PIN . '%')
        ->arg('$hsmCaCert', '%' . BridgeConstantsInterface::PARAM_AWS_PKCS11_HSM_CA_CERT . '%')
        ->arg('$disableKeyAvailabilityCheck', '%' . BridgeConstantsInterface::PARAM_AWS_PKCS11_DISABLE_KEY_AVAILABILITY_CHECK . '%')
        ->arg('$hsmIpAddress', '%' . BridgeConstantsInterface::PARAM_AWS_PKCS11_HSM_IP_ADDRESS . '%')
        ->arg('$cloudHsmClusterId', '%' . BridgeConstantsInterface::PARAM_AWS_PKCS11_CLOUD_HSM_CLUSTER_ID . '%')
        ->arg('$awsRegion', '%' . BridgeConstantsInterface::PARAM_AWS_PKCS11_AWS_REGION . '%')
        ->arg('$aad', '%' . BridgeConstantsInterface::PARAM_AWS_PKCS11_AAD . '%')
        ->arg('$serverClientCertFile', '%' . BridgeConstantsInterface::PARAM_AWS_PKCS11_SERVER_CLIENT_CERT_FILE . '%')
        ->arg('$serverClientKeyFile', '%' . BridgeConstantsInterface::PARAM_AWS_PKCS11_SERVER_CLIENT_KEY_FILE . '%')
        ->arg('$awsCloudHsmSdkOptions', '%' . BridgeConstantsInterface::PARAM_AWS_PKCS11_HSM_SDK_OPTIONS . '%')
        ->arg('$defaultKeyName', '%' . BridgeConstantsInterface::PARAM_DEFAULT_KEY_NAME . '%');
};
