<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Bridge\Laravel;

use EonX\EasyEncryption\Interfaces\EncryptorInterface;

final class EasyEncryptionServiceProviderTest extends AbstractLaravelTestCase
{
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
