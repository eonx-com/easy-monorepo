<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyEncryption\Bundle\Enum\ConfigParam;
use EonX\EasyEncryption\Bundle\Enum\ConfigTag;
use EonX\EasyEncryption\Common\Encryptor\Encryptor;
use EonX\EasyEncryption\Common\Encryptor\EncryptorInterface;
use EonX\EasyEncryption\Common\Factory\DefaultEncryptionKeyFactory;
use EonX\EasyEncryption\Common\Factory\EncryptionKeyFactoryInterface;
use EonX\EasyEncryption\Common\Provider\DefaultEncryptionKeyProvider;
use EonX\EasyEncryption\Common\Provider\EncryptionKeyProviderInterface;
use EonX\EasyEncryption\Encryptable\Encryptor\ObjectEncryptor;
use EonX\EasyEncryption\Encryptable\Encryptor\ObjectEncryptorInterface;
use EonX\EasyEncryption\Encryptable\Encryptor\StringEncryptor;
use EonX\EasyEncryption\Encryptable\Encryptor\StringEncryptorInterface;
use EonX\EasyEncryption\Encryptable\HashCalculator\HashCalculatorInterface;
use EonX\EasyEncryption\Encryptable\HashCalculator\HmacSha512HashCalculator;
use EonX\EasyEncryption\Encryptable\Listener\DoctrineEncryptionListener;

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
        ->arg('$keyResolvers', tagged_iterator(ConfigTag::EncryptionKeyResolver->value));

    // Encryptor
    $services
        ->set(EncryptorInterface::class, Encryptor::class)
        ->arg('$defaultKeyName', param(ConfigParam::DefaultKeyName->value));

    $services->set(HashCalculatorInterface::class, HmacSha512HashCalculator::class)
        ->arg('$secret', env('APP_SECRET'));

    $services->set(StringEncryptorInterface::class, StringEncryptor::class)
        ->arg('$encryptor', service(EncryptorInterface::class))
        ->arg('$encryptionKeyName', param(ConfigParam::DefaultKeyName->value))
        ->arg('$maxChunkSize', param(ConfigParam::MaxChunkSize->value));

    $services->set(ObjectEncryptorInterface::class, ObjectEncryptor::class);

    $services->set(DoctrineEncryptionListener::class);
};
