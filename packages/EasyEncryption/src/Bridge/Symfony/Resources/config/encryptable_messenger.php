<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyEncryption\Bridge\BridgeConstantsInterface;
use EonX\EasyEncryption\Serializers\EncryptableAwareMessengerSerializer;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(EncryptableAwareMessengerSerializer::class)
        ->arg('$serializer', service('messenger.transport.native_php_serializer'))
        ->arg('$fullyEncryptedMessages', param(BridgeConstantsInterface::PARAM_FULLY_ENCRYPTED_MESSAGES));
};
