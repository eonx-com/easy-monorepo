<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Transformers;

use EonX\EasyBatch\Objects\BatchItem;
use EonX\EasyBatch\Serializers\MessageSerializer;
use EonX\EasyBatch\Tests\AbstractTestCase;
use EonX\EasyBatch\Transformers\BatchItemTransformer;
use EonX\EasyEncryption\Encryptor;
use EonX\EasyEncryption\Factories\DefaultEncryptionKeyFactory;
use EonX\EasyEncryption\Interfaces\EncryptorInterface;
use EonX\EasyEncryption\Providers\DefaultEncryptionKeyProvider;
use EonX\EasyEncryption\Resolvers\SimpleEncryptionKeyResolver;

final class BatchItemTransformerTest extends AbstractTestCase
{
    public function testEncryptedBatchItem(): void
    {
        $message = new \stdClass();
        $message->key = 'value';

        $batchItem = $this->getBatchItemFactory()->create('batchId', $message);
        $batchItem->setEncrypted(true);

        $transformer = new BatchItemTransformer(new MessageSerializer());
        $transformer->setEncryptor($this->getEncryptor());

        $array = $transformer->transformToArray($batchItem);
        /** @var \EonX\EasyBatch\Interfaces\BatchItemInterface $newBatchItem */
        $newBatchItem = $transformer->transformToObject($array);

        self::assertTrue($array['encrypted']);
        self::assertEquals(EncryptorInterface::DEFAULT_KEY_NAME, $newBatchItem->getEncryptionKeyName());
        self::assertInstanceOf(\stdClass::class, $newBatchItem->getMessage());
    }


    private function getEncryptor(): EncryptorInterface
    {
        $keyResolver = new SimpleEncryptionKeyResolver(
            EncryptorInterface::DEFAULT_KEY_NAME,
            'TwzQsKkBcVlYYRQDvwgiGZemFVbpNiCr'
        );
        $keyFactory = new DefaultEncryptionKeyFactory();
        $keyProvider = new DefaultEncryptionKeyProvider($keyFactory, [$keyResolver]);

        return new Encryptor($keyFactory, $keyProvider);
    }
}
