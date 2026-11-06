<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyEncryption\Bundle\Enum\ConfigParam;
use EonX\EasyEncryption\Encryptable\Serializer\EncryptableAwareMessengerSerializer;
use EonX\EasyEncryption\Encryptable\Signer\HmacMessengerEnvelopeSigner;
use EonX\EasyEncryption\Encryptable\Signer\MessengerEnvelopeSignerInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(MessengerEnvelopeSignerInterface::class, HmacMessengerEnvelopeSigner::class)
        ->arg('$signingKeys', param(ConfigParam::MessengerSigningKeys->value));

    $services->set(EncryptableAwareMessengerSerializer::class)
        ->arg('$serializer', service('messenger.transport.native_php_serializer'))
        ->arg('$signer', service(MessengerEnvelopeSignerInterface::class))
        ->arg('$fullyEncryptedMessages', param(ConfigParam::FullyEncryptedMessages->value))
        ->arg('$allowUnsignedMessages', param(ConfigParam::MessengerAllowUnsignedMessages->value));
};
