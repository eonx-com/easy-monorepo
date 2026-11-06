<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Unit\Bundle;

use EonX\EasyBatch\Common\Exception\BatchItemMessageSignatureInvalidException;
use EonX\EasyBatch\Common\Serializer\MessageSerializer;
use EonX\EasyBatch\Common\Serializer\MessageSerializerInterface;
use EonX\EasyBatch\Common\Signer\HmacMessageSigner;
use EonX\EasyBatch\Tests\Stub\Kernel\KernelStub;
use EonX\EasyBatch\Tests\Unit\AbstractUnitTestCase;
use stdClass;
use Symfony\Component\Filesystem\Filesystem;

final class EasyBatchBundleMessageSigningTest extends AbstractUnitTestCase
{
    private const string SIGNING_CONFIG = __DIR__ . '/../../Fixture/config/signing.php';

    protected function tearDown(): void
    {
        $filesystem = new Filesystem();
        $filesystem->remove(__DIR__ . '/../../../var');

        parent::tearDown();
    }

    public function testItRejectsUnsignedMessageWhenConfigured(): void
    {
        $sut = $this->getMessageSerializer([self::SIGNING_CONFIG]);
        $this->expectException(BatchItemMessageSignatureInvalidException::class);
        $this->expectExceptionMessage('Batch item message signature is missing.');

        $sut->unserialize(\addslashes(\serialize(new stdClass())));
    }

    public function testItSignsWithConfiguredSecrets(): void
    {
        $sut = $this->getMessageSerializer([self::SIGNING_CONFIG]);
        $messageOfOldSecret = new MessageSerializer(new HmacMessageSigner('old-secret'))
            ->serialize(new stdClass());

        $verifier = new MessageSerializer(signer: new HmacMessageSigner('new-secret'), allowUnsignedMessages: false);
        $newSecretResult = $verifier->unserialize($sut->serialize(new stdClass()));
        $oldSecretResult = $sut->unserialize($messageOfOldSecret);

        self::assertEquals(new stdClass(), $newSecretResult);
        self::assertEquals(new stdClass(), $oldSecretResult);
    }

    public function testItSignsWithKernelSecretAndAcceptsUnsignedMessageByDefault(): void
    {
        $sut = $this->getMessageSerializer();

        $verifier = new MessageSerializer(signer: new HmacMessageSigner('kernel-secret'), allowUnsignedMessages: false);
        $signedResult = $verifier->unserialize($sut->serialize(new stdClass()));
        $unsignedResult = $sut->unserialize(\addslashes(\serialize(new stdClass())));

        self::assertEquals(new stdClass(), $signedResult);
        self::assertEquals(new stdClass(), $unsignedResult);
    }

    /**
     * @param string[] $configs
     */
    private function getMessageSerializer(array $configs = []): MessageSerializerInterface
    {
        $kernel = new KernelStub('test', true, $configs);
        $kernel->boot();

        return $kernel->getContainer()
            ->get(MessageSerializerInterface::class);
    }
}
