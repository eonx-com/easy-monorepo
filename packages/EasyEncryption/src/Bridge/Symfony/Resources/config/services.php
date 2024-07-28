<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyEncryption\Bridge\BridgeConstantsInterface;
use EonX\EasyEncryption\Encryptor\EncryptableEncryptor;
use EonX\EasyEncryption\Encryptor\Encryptor;
use EonX\EasyEncryption\Factories\DefaultEncryptionKeyFactory;
use EonX\EasyEncryption\HashCalculator\AwsPkcs11HashCalculator;
use EonX\EasyEncryption\HashCalculator\HashCalculatorInterface;
use EonX\EasyEncryption\Interfaces\AwsPkcs11EncryptorInterface;
use EonX\EasyEncryption\Interfaces\EncryptionKeyFactoryInterface;
use EonX\EasyEncryption\Interfaces\EncryptionKeyProviderInterface;
use EonX\EasyEncryption\Interfaces\EncryptorInterface;
use EonX\EasyEncryption\Listener\DoctrineOnFlushEncryptionListener;
use EonX\EasyEncryption\Listener\DoctrinePostLoadEncryptionListener;
use EonX\EasyEncryption\LocalEncryptor;
use EonX\EasyEncryption\Metadata\EncryptableMetadata;
use EonX\EasyEncryption\Providers\DefaultEncryptionKeyProvider;
use EonX\EasyEncryption\Serializer\EncryptableAwareMessengerSerializer;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Factory
    $services->set(EncryptionKeyFactoryInterface::class, DefaultEncryptionKeyFactory::class);

    // Provider
    $services
        ->set(EncryptionKeyProviderInterface::class, DefaultEncryptionKeyProvider::class)
        ->arg('$keyResolvers', tagged_iterator(BridgeConstantsInterface::TAG_ENCRYPTION_KEY_RESOLVER));

    // Encryptor
    $services
        ->set(EncryptorInterface::class, LocalEncryptor::class)
        ->arg('$defaultKeyName', param(BridgeConstantsInterface::PARAM_DEFAULT_KEY_NAME));

    $services->set(Encryptor::class)
        ->arg('$encryptor', service(AwsPkcs11EncryptorInterface::class))
        ->arg('$encryptionKeyName', param(BridgeConstantsInterface::PARAM_DEFAULT_KEY_NAME))
        ->arg('$maxChunkSize', param(BridgeConstantsInterface::PARAM_MAX_CHUNK_SIZE));

    $services->set(HashCalculatorInterface::class, AwsPkcs11HashCalculator::class)
        ->arg('$signKeyName', param(BridgeConstantsInterface::PARAM_DEFAULT_KEY_NAME));

    $services->set(EncryptableAwareMessengerSerializer::class)
        ->arg('$serializer', service('messenger.transport.native_php_serializer'))
        ->arg('$fullyEncryptedMessages', param(BridgeConstantsInterface::PARAM_FULLY_ENCRYPTED_MESSAGES));

    $services->set(EncryptableMetadata::class);
    $services->set(EncryptableEncryptor::class);
    $services->set(DoctrineOnFlushEncryptionListener::class);
    $services->set(DoctrinePostLoadEncryptionListener::class);
};
