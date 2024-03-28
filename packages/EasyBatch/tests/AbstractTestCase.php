<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Tests;

use EonX\EasyBatch\Factories\BatchFactory;
use EonX\EasyBatch\Factories\BatchItemFactory;
use EonX\EasyBatch\IdStrategies\UuidStrategy;
use EonX\EasyBatch\Interfaces\BatchFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchItemFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchObjectIdStrategyInterface;
use EonX\EasyBatch\Serializers\MessageSerializer;
use EonX\EasyBatch\Transformers\BatchItemTransformer;
use EonX\EasyBatch\Transformers\BatchTransformer;
use EonX\EasyRandom\Bridge\Uid\UuidGenerator;
use EonX\EasyRandom\Generators\RandomGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Uid\Factory\UuidFactory;

abstract class AbstractTestCase extends TestCase
{
    private ?BatchFactoryInterface $batchFactory = null;

    private ?BatchItemFactoryInterface $batchItemFactory = null;

    private ?BatchObjectIdStrategyInterface $batchObjectIdStrategy = null;

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $var = __DIR__ . '/../var';

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

        $this->batchFactory = new BatchFactory(new BatchTransformer());

        return $this->batchFactory;
    }

    protected function getBatchItemFactory(): BatchItemFactoryInterface
    {
        if ($this->batchItemFactory !== null) {
            return $this->batchItemFactory;
        }

        $this->batchItemFactory = new BatchItemFactory(
            new BatchItemTransformer(new MessageSerializer())
        );

        return $this->batchItemFactory;
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
