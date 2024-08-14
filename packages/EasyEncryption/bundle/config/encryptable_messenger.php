<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyEncryption\Bundle\Enum\ConfigParam;
use EonX\EasyEncryption\Encryptable\Metadata\EncryptableMetadata;
use EonX\EasyEncryption\Encryptable\Metadata\EncryptableMetadataInterface;
use EonX\EasyEncryption\Encryptable\Serializer\EncryptableAwareMessengerSerializer;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(EncryptableMetadataInterface::class, EncryptableMetadata::class);

    $services->set(EncryptableAwareMessengerSerializer::class)
        ->arg('$serializer', service('messenger.transport.native_php_serializer'))
        ->arg('$fullyEncryptedMessages', param(ConfigParam::FullyEncryptedMessages->value));
};
