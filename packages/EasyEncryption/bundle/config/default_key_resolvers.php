<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyEncryption\Bundle\Enum\ConfigParam;
use EonX\EasyEncryption\Bundle\Enum\ConfigServiceId;
use EonX\EasyEncryption\Common\Resolver\SimpleEncryptionKeyResolver;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(ConfigServiceId::DefaultKeyResolver->value, SimpleEncryptionKeyResolver::class)
        ->arg('$keyName', param(ConfigParam::DefaultKeyName->value))
        ->arg('$encryptionKey', param(ConfigParam::DefaultEncryptionKey->value))
        ->arg('$salt', param(ConfigParam::DefaultSalt->value));
};
