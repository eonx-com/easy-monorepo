<?php

declare(strict_types=1);

use EonX\EasyEncryption\Bridge\BridgeConstantsInterface;
use EonX\EasyEncryption\Encryptor;
use EonX\EasyEncryption\Factories\DefaultEncryptionKeyFactory;
use EonX\EasyEncryption\Interfaces\EncryptionKeyFactoryInterface;
use EonX\EasyEncryption\Interfaces\EncryptionKeyProviderInterface;
use EonX\EasyEncryption\Interfaces\EncryptorInterface;
use EonX\EasyEncryption\Providers\DefaultEncryptionKeyProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

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
        ->arg('$defaultKeyName', '%' . BridgeConstantsInterface::PARAM_DEFAULT_KEY_NAME . '%');
};
