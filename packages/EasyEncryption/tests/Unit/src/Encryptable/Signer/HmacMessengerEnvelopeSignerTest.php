<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Unit\Encryptable\Signer;

use EonX\EasyEncryption\Encryptable\Signer\HmacMessengerEnvelopeSigner;
use EonX\EasyEncryption\Tests\Unit\AbstractUnitTestCase;
use InvalidArgumentException;

final class HmacMessengerEnvelopeSignerTest extends AbstractUnitTestCase
{
    public function testConstructFailsWithNoUsableKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one non-empty signing key must be provided.');

        new HmacMessengerEnvelopeSigner(['', '']);
    }

    public function testSignUsesTheFirstKey(): void
    {
        $sut = new HmacMessengerEnvelopeSigner(['first-key', 'second-key']);

        self::assertSame(\hash_hmac('sha512', 'payload', 'first-key'), $sut->sign('payload'));
    }

    public function testVerifyAcceptsAnyKeyInTheList(): void
    {
        $signedWithOld = (new HmacMessengerEnvelopeSigner('old-key'))->sign('payload');
        $sut = new HmacMessengerEnvelopeSigner(['new-key', 'old-key']);

        self::assertTrue($sut->verify('payload', $sut->sign('payload')));
        self::assertTrue($sut->verify('payload', $signedWithOld));
    }

    public function testVerifyRejectsAKeyOutsideTheList(): void
    {
        $signedWithRemovedKey = (new HmacMessengerEnvelopeSigner('removed-key'))->sign('payload');
        $sut = new HmacMessengerEnvelopeSigner(['new-key']);

        self::assertFalse($sut->verify('payload', $signedWithRemovedKey));
    }
}
