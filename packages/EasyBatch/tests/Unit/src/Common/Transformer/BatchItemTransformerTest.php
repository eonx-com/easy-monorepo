<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Unit\Common\Transformer;

use EonX\EasyBatch\Common\ValueObject\BatchItem;
use EonX\EasyBatch\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyEncryption\Common\Encryptor\Encryptor;
use EonX\EasyEncryption\Common\Encryptor\EncryptorInterface;
use EonX\EasyEncryption\Common\Factory\DefaultEncryptionKeyFactory;
use EonX\EasyEncryption\Common\Provider\DefaultEncryptionKeyProvider;
use EonX\EasyEncryption\Common\Resolver\SimpleEncryptionKeyResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;

final class BatchItemTransformerTest extends AbstractUnitTestCase
{
    public static function provideEncryptedBatchItemClass(): iterable
    {
        yield 'Current BatchItem' => [
            'class' => BatchItem::class,
        ];

        yield 'Old BatchItem' => [
            'class' => 'EonX\EasyBatch\Objects\BatchItem',
        ];
    }

    /**
     * @see testEncryptedBatchItem
     */
    public static function provideEncryptedBatchItemData(): iterable
    {
        yield 'Encrypted' => [true];
        yield 'Not Encrypted' => [false];
    }

    #[DataProvider('provideEncryptedBatchItemData')]
    public function testEncryptedBatchItem(bool $encrypted): void
    {
        $message = new stdClass();
        $message->key = 'value';

        $batchItem = $this->getBatchItemFactory()
            ->create('batchId', $message);
        $batchItem->setId('my-id');
        $batchItem->setEncrypted($encrypted);

        $transformer = $this->getBatchItemTransformer();
        if ($encrypted) {
            $transformer->setEncryptor($this->getEncryptor());
        }

        $array = $transformer->transformToArray($batchItem);
        /** @var \EonX\EasyBatch\Common\ValueObject\BatchItem $newBatchItem */
        $newBatchItem = $transformer->transformToObject($array);
        $expectedEncryptionKeyName = $encrypted ? 'app' : null;

        self::assertEquals($encrypted, $array['encrypted']);
        self::assertEquals($encrypted, $newBatchItem->isEncrypted());
        self::assertEquals($expectedEncryptionKeyName, $newBatchItem->getEncryptionKeyName());
        self::assertInstanceOf(stdClass::class, $newBatchItem->getMessage());
    }

    #[DataProvider('provideEncryptedBatchItemClass')]
    public function testInstantiateForClass(string $class): void
    {
        $transformer = $this->getBatchItemTransformer();
        $batchItem = $transformer->instantiateForClass($class);

        self::assertInstanceOf(BatchItem::class, $batchItem);
    }

    private function getEncryptor(): EncryptorInterface
    {
        $keyResolver = new SimpleEncryptionKeyResolver('app', 'TwzQsKkBcVlYYRQDvwgiGZemFVbpNiCr');
        $keyFactory = new DefaultEncryptionKeyFactory();
        $keyProvider = new DefaultEncryptionKeyProvider($keyFactory, [$keyResolver]);

        return new Encryptor($keyFactory, $keyProvider);
    }
}
