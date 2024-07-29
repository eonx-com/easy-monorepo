<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyEncryption\Bridge\BridgeConstantsInterface;
use EonX\EasyEncryption\Encryptor\EncryptableEncryptor;
use EonX\EasyEncryption\Factories\DefaultEncryptionKeyFactory;
use EonX\EasyEncryption\Interfaces\EncryptionKeyFactoryInterface;
use EonX\EasyEncryption\Interfaces\EncryptionKeyProviderInterface;
use EonX\EasyEncryption\Interfaces\EncryptorInterface;
use EonX\EasyEncryption\Listener\DoctrineOnFlushEncryptionListener;
use EonX\EasyEncryption\Listener\DoctrinePostLoadEncryptionListener;
use EonX\EasyEncryption\LocalEncryptor;
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
        ->set(EncryptorInterface::class, LocalEncryptor::class)
        ->arg('$defaultKeyName', param(BridgeConstantsInterface::PARAM_DEFAULT_KEY_NAME));

    $services->set(LocalEncryptor::class);
    $services->set(EncryptableMetadata::class);
    $services->set(EncryptableEncryptor::class);
    $services->set(DoctrineOnFlushEncryptionListener::class);
    $services->set(DoctrinePostLoadEncryptionListener::class);
};
