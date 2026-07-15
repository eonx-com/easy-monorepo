<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Unit\Bundle;

use EonX\EasyEncryption\Common\Encryptor\EncryptorInterface;
use EonX\EasyEncryption\Encryptable\HashCalculator\HashCalculatorInterface;
use EonX\EasyEncryption\Encryptable\HashCalculator\HmacSha512HashCalculator;
use EonX\EasyEncryption\Encryptable\Hasher\EncryptableFieldHasherInterface;
use EonX\EasyEncryption\Tests\Stub\Entity\EncryptableEntityStub;
use EonX\EasyEncryption\Tests\Unit\AbstractSymfonyTestCase;

final class EasyEncryptionBundleTest extends AbstractSymfonyTestCase
{
    public function testItSucceedsWithDefaultHashNormalisation(): void
    {
        $this->setAppSecret('f42a3968db6a957300c4f0c46a341c80');
        $container = $this->getKernel()
            ->getContainer();
        $hashCalculator = $container->get(HashCalculatorInterface::class);

        $fieldHasher = $container->get(EncryptableFieldHasherInterface::class);
        $hash = $fieldHasher->hashForField(EncryptableEntityStub::class, 'email', 'John.Doe@Example.com');

        self::assertInstanceOf(HmacSha512HashCalculator::class, $hashCalculator);
        self::assertSame($hashCalculator->calculate('john.doe@example.com'), $hash);
    }

    public function testItSucceedsWithFieldOverrideRegardlessOfGlobalDefault(): void
    {
        $this->setAppSecret('f42a3968db6a957300c4f0c46a341c80');
        $container = $this->getKernel()
            ->getContainer();
        $hashCalculator = $container->get(HashCalculatorInterface::class);

        $fieldHasher = $container->get(EncryptableFieldHasherInterface::class);
        $hash = $fieldHasher->hashForField(EncryptableEntityStub::class, 'caseSensitiveCode', 'AbC123');

        self::assertSame($hashCalculator->calculate('AbC123'), $hash);
    }

    public function testSanity(): void
    {
        $this->setAppSecret('f42a3968db6a957300c4f0c46a341c80');

        $container = $this->getKernel()
            ->getContainer();
        $encryptor = $container->get(EncryptorInterface::class);
        $message = 'my message to encrypt';

        self::assertInstanceOf(EncryptorInterface::class, $encryptor);
        self::assertEquals($message, $encryptor->decrypt($encryptor->encrypt($message)));
    }
}
