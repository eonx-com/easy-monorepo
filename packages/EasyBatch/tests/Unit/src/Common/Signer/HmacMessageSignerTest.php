<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Unit\Common\Signer;

use EonX\EasyBatch\Common\Signer\HmacMessageSigner;
use EonX\EasyBatch\Tests\Unit\AbstractUnitTestCase;
use InvalidArgumentException;

final class HmacMessageSignerTest extends AbstractUnitTestCase
{
    public function testItRejectsSignatureOfRemovedSecret(): void
    {
        $removedSecretSigner = new HmacMessageSigner('removed-secret');
        $signature = $removedSecretSigner->sign('payload');
        $sut = new HmacMessageSigner(['new-secret']);

        $result = $sut->verify('payload', $signature);

        self::assertFalse($result);
    }

    public function testItSignsWithFirstSecret(): void
    {
        $sut = new HmacMessageSigner(['first-secret', 'second-secret']);

        $result = $sut->sign('payload');

        self::assertSame(
            \hash_hmac('sha256', 'payload', \hash_hmac('sha256', 'easy-batch:message:v1', 'first-secret')),
            $result
        );
    }

    public function testItThrowsExceptionWithEmptySecret(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Signing secret must not be empty.');

        new HmacMessageSigner(['secret', '']);
    }

    public function testItThrowsExceptionWithNoSecret(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one signing secret must be provided.');

        new HmacMessageSigner([]);
    }

    public function testItVerifiesWithAnySecretInList(): void
    {
        $oldSecretSigner = new HmacMessageSigner('old-secret');
        $signatureOfOldSecret = $oldSecretSigner->sign('payload');
        $sut = new HmacMessageSigner(['new-secret', 'old-secret']);

        $resultForNewSecret = $sut->verify('payload', $sut->sign('payload'));
        $resultForOldSecret = $sut->verify('payload', $signatureOfOldSecret);

        self::assertTrue($resultForNewSecret);
        self::assertTrue($resultForOldSecret);
    }
}
