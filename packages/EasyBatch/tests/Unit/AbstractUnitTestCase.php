<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Unit;

use EonX\EasyBatch\Common\Factory\BatchFactory;
use EonX\EasyBatch\Common\Factory\BatchFactoryInterface;
use EonX\EasyBatch\Common\Factory\BatchItemFactory;
use EonX\EasyBatch\Common\Factory\BatchItemFactoryInterface;
use EonX\EasyBatch\Common\Serializer\MessageSerializer;
use EonX\EasyBatch\Common\Strategy\BatchObjectIdStrategyInterface;
use EonX\EasyBatch\Common\Strategy\UuidStrategy;
use EonX\EasyBatch\Common\Transformer\BatchItemTransformer;
use EonX\EasyBatch\Common\Transformer\BatchTransformer;
use EonX\EasyBatch\Common\ValueObject\Batch;
use EonX\EasyBatch\Common\ValueObject\BatchItem;
use EonX\EasyRandom\Generator\RandomGenerator;
use EonX\EasyRandom\Generator\UuidGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Uid\Factory\UuidFactory;

abstract class AbstractUnitTestCase extends TestCase
{
    private ?BatchFactoryInterface $batchFactory = null;

    private ?BatchItemFactoryInterface $batchItemFactory = null;

    private ?BatchObjectIdStrategyInterface $batchObjectIdStrategy = null;

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $var = __DIR__ . '/../../var';

        if ($fs->exists($var)) {
            $fs->remove($var);
        }

        parent::tearDown();
    }

    protected function getBatchFactory(): BatchFactoryInterface
    {
        if ($this->batchFactory !== null) {
            return $this->batchFactory;
        }

        $this->batchFactory = new BatchFactory($this->getBatchTransformer());

        return $this->batchFactory;
    }

    protected function getBatchItemFactory(): BatchItemFactoryInterface
    {
        if ($this->batchItemFactory !== null) {
            return $this->batchItemFactory;
        }

        $this->batchItemFactory = new BatchItemFactory($this->getBatchItemTransformer());

        return $this->batchItemFactory;
    }

    protected function getBatchItemTransformer(): BatchItemTransformer
    {
        return new BatchItemTransformer(
            new MessageSerializer(),
            BatchItem::class,
            'Y-m-d H:i:s.u'
        );
    }

    protected function getBatchTransformer(): BatchTransformer
    {
        return new BatchTransformer(
            Batch::class,
            'Y-m-d H:i:s.u'
        );
    }

    protected function getIdStrategy(): BatchObjectIdStrategyInterface
    {
        if ($this->batchObjectIdStrategy !== null) {
            return $this->batchObjectIdStrategy;
        }

        $this->batchObjectIdStrategy = new UuidStrategy(
            new RandomGenerator(new UuidGenerator(new UuidFactory()))
        );

        return $this->batchObjectIdStrategy;
    }
}
