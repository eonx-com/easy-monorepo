<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyEncryption\Bridge\BridgeConstantsInterface;
use EonX\EasyEncryption\Resolvers\SimpleEncryptionKeyResolver;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(BridgeConstantsInterface::SERVICE_DEFAULT_KEY_RESOLVER, SimpleEncryptionKeyResolver::class)
        ->arg('$keyName', param(BridgeConstantsInterface::PARAM_DEFAULT_KEY_NAME))
        ->arg('$encryptionKey', param(BridgeConstantsInterface::PARAM_DEFAULT_ENCRYPTION_KEY))
        ->arg('$salt', param(BridgeConstantsInterface::PARAM_DEFAULT_SALT));
};
