<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyEncryption\Bridge\BridgeConstantsInterface;
use EonX\EasyEncryption\Encryptor;
use EonX\EasyEncryption\Encryptors\ObjectEncryptor;
use EonX\EasyEncryption\Encryptors\StringEncryptor;
use EonX\EasyEncryption\Factories\DefaultEncryptionKeyFactory;
use EonX\EasyEncryption\HashCalculators\HashCalculatorInterface;
use EonX\EasyEncryption\HashCalculators\HmacSha512HashCalculator;
use EonX\EasyEncryption\Interfaces\EncryptionKeyFactoryInterface;
use EonX\EasyEncryption\Interfaces\EncryptionKeyProviderInterface;
use EonX\EasyEncryption\Interfaces\EncryptorInterface;
use EonX\EasyEncryption\Listeners\DoctrineEncryptionListener;
use EonX\EasyEncryption\Metadata\EncryptableMetadata;
use EonX\EasyEncryption\Providers\DefaultEncryptionKeyProvider;

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
        ->set(EncryptorInterface::class, Encryptor::class)
        ->arg('$defaultKeyName', param(BridgeConstantsInterface::PARAM_DEFAULT_KEY_NAME));

    $services->set(HashCalculatorInterface::class, HmacSha512HashCalculator::class);

    $services->set(StringEncryptor::class)
        ->arg('$encryptor', service(Encryptor::class))
        ->arg('$encryptionKeyName', param(BridgeConstantsInterface::PARAM_DEFAULT_KEY_NAME))
        ->arg('$maxChunkSize', param(BridgeConstantsInterface::PARAM_MAX_CHUNK_SIZE));

    $services->set(Encryptor::class);
    $services->set(EncryptableMetadata::class);
    $services->set(ObjectEncryptor::class);
    $services->set(DoctrineEncryptionListener::class);
};
