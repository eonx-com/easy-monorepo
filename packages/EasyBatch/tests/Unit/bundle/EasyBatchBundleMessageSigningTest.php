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
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Filesystem\Filesystem;

final class EasyBatchBundleMessageSigningTest extends AbstractUnitTestCase
{
    private const string SIGNING_CONFIG = __DIR__ . '/../../Fixture/config/signing.php';

    private const string UNSIGNED_MESSAGES_REFUSED_CONFIG = __DIR__
        . '/../../Fixture/config/unsigned_messages_refused.php';

    protected function tearDown(): void
    {
        $filesystem = new Filesystem();
        $filesystem->remove(__DIR__ . '/../../../var');

        parent::tearDown();
    }

    public function testItAcceptsUnsignedMessageWhenConfigured(): void
    {
        $sut = $this->getMessageSerializer([self::SIGNING_CONFIG]);

        $result = $sut->unserialize(\addslashes(\serialize(new stdClass())));

        self::assertEquals(new stdClass(), $result);
    }

    public function testItRejectsUnsignedMessageWhenNotAllowed(): void
    {
        $sut = $this->getMessageSerializer([self::UNSIGNED_MESSAGES_REFUSED_CONFIG]);
        $this->expectException(BatchItemMessageSignatureInvalidException::class);
        $this->expectExceptionMessage('Batch item message signature is missing.');

        $sut->unserialize(\addslashes(\serialize(new stdClass())));
    }

    public function testItRequiresAllowUnsignedMessagesToBeConfigured(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            'The child config "allow_unsigned_messages" under "easy_batch" must be configured'
        );

        $this->getMessageSerializer([]);
    }

    public function testItSignsWithConfiguredSecrets(): void
    {
        $sut = $this->getMessageSerializer([self::SIGNING_CONFIG]);
        $messageOfOldSecret = new MessageSerializer(new HmacMessageSigner('old-secret'))
            ->serialize(new stdClass());
        $verifier = new MessageSerializer(new HmacMessageSigner('new-secret'));

        $newSecretResult = $verifier->unserialize($sut->serialize(new stdClass()));
        $oldSecretResult = $sut->unserialize($messageOfOldSecret);

        self::assertEquals(new stdClass(), $newSecretResult);
        self::assertEquals(new stdClass(), $oldSecretResult);
    }

    public function testItSignsWithKernelSecretByDefault(): void
    {
        $sut = $this->getMessageSerializer([self::UNSIGNED_MESSAGES_REFUSED_CONFIG]);
        $verifier = new MessageSerializer(new HmacMessageSigner('kernel-secret'));

        $result = $verifier->unserialize($sut->serialize(new stdClass()));

        self::assertEquals(new stdClass(), $result);
    }

    /**
     * @param string[] $configs
     */
    private function getMessageSerializer(array $configs): MessageSerializerInterface
    {
        $kernel = new KernelStub('test', true, $configs);
        $kernel->boot();

        return $kernel->getContainer()
            ->get(MessageSerializerInterface::class);
    }
}
