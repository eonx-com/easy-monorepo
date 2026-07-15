<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Unit\Laravel;

use EonX\EasyEncryption\AwsCloudHsm\Encryptor\AwsCloudHsmEncryptorInterface;
use EonX\EasyEncryption\Common\Encryptor\EncryptorInterface;
use EonX\EasyEncryption\Encryptable\HashCalculator\HashCalculatorInterface;
use EonX\EasyEncryption\Encryptable\HashCalculator\HmacSha512HashCalculator;
use EonX\EasyEncryption\Encryptable\Hasher\EncryptableFieldHasherInterface;
use EonX\EasyEncryption\Tests\Stub\Encryptor\AwsCloudHsmEncryptorStub;
use EonX\EasyEncryption\Tests\Stub\Entity\EncryptableEntityStub;

final class EasyEncryptionServiceProviderTest extends AbstractLaravelTestCase
{
    public function testItSucceedsWithDefaultHashNormalisation(): void
    {
        $this->setAppKey('f42a3968db6a957300c4f0c46a341c80');
        $app = $this->getApplication();
        $hashCalculator = $app->make(HashCalculatorInterface::class);

        $fieldHasher = $app->make(EncryptableFieldHasherInterface::class);
        $hash = $fieldHasher->hashForField(EncryptableEntityStub::class, 'email', 'John.Doe@Example.com');

        self::assertInstanceOf(HmacSha512HashCalculator::class, $hashCalculator);
        self::assertSame($hashCalculator->calculate('john.doe@example.com'), $hash);
    }

    public function testItSucceedsWithDefaultHashNormalisationWithAwsCloudHsm(): void
    {
        $this->setAppKey('f42a3968db6a957300c4f0c46a341c80');
        $app = $this->getApplication([
            'easy-encryption.aws_cloud_hsm_encryptor.enabled' => true,
        ]);
        $app->singleton(
            AwsCloudHsmEncryptorInterface::class,
            static fn (): AwsCloudHsmEncryptorInterface => new AwsCloudHsmEncryptorStub()
        );
        $hashCalculator = $app->make(HashCalculatorInterface::class);

        $fieldHasher = $app->make(EncryptableFieldHasherInterface::class);
        $hash = $fieldHasher->hashForField(EncryptableEntityStub::class, 'email', 'John.Doe@Example.com');

        self::assertSame($hashCalculator->calculate('john.doe@example.com'), $hash);
    }

    public function testItSucceedsWithFieldOverrideRegardlessOfGlobalDefault(): void
    {
        $this->setAppKey('f42a3968db6a957300c4f0c46a341c80');
        $app = $this->getApplication([
            'easy-encryption.aws_cloud_hsm_encryptor.enabled' => true,
        ]);
        $app->singleton(
            AwsCloudHsmEncryptorInterface::class,
            static fn (): AwsCloudHsmEncryptorInterface => new AwsCloudHsmEncryptorStub()
        );
        $hashCalculator = $app->make(HashCalculatorInterface::class);

        $fieldHasher = $app->make(EncryptableFieldHasherInterface::class);
        $hash = $fieldHasher->hashForField(EncryptableEntityStub::class, 'caseSensitiveCode', 'AbC123');

        self::assertSame($hashCalculator->calculate('AbC123'), $hash);
    }

    public function testSanity(): void
    {
        $this->setAppKey('f42a3968db6a957300c4f0c46a341c80');

        $app = $this->getApplication();
        $encryptor = $app->get(EncryptorInterface::class);
        $message = 'my message to encrypt';

        self::assertInstanceOf(EncryptorInterface::class, $encryptor);
        self::assertEquals($message, $encryptor->decrypt($encryptor->encrypt($message)));
    }
}
