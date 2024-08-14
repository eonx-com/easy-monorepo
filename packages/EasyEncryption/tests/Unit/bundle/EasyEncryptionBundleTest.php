<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Unit\Bundle;

use EonX\EasyEncryption\Common\Encryptor\EncryptorInterface;
use EonX\EasyEncryption\Tests\Unit\AbstractSymfonyTestCase;

final class EasyEncryptionBundleTest extends AbstractSymfonyTestCase
{
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
