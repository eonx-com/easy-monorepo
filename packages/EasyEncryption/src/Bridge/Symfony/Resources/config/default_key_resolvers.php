<?php

declare(strict_types=1);

use EonX\EasyEncryption\Bridge\BridgeConstantsInterface;
use EonX\EasyEncryption\Resolvers\SimpleEncryptionKeyResolver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(BridgeConstantsInterface::SERVICE_DEFAULT_KEY_RESOLVER, SimpleEncryptionKeyResolver::class)
        ->arg('$keyName', '%' . BridgeConstantsInterface::PARAM_DEFAULT_KEY_NAME . '%')
        ->arg('$encryptionKey', '%' . BridgeConstantsInterface::PARAM_DEFAULT_ENCRYPTION_KEY . '%')
        ->arg('$salt', '%' . BridgeConstantsInterface::PARAM_DEFAULT_SALT . '%');
};
