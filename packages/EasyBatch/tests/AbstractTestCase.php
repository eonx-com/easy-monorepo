<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests;

use EonX\EasyBatch\Factories\BatchItemFactory;
use EonX\EasyBatch\IdStrategies\UuidV4Strategy;
use EonX\EasyBatch\Interfaces\BatchItemFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchObjectIdStrategyInterface;
use EonX\EasyBatch\Serializers\MessageSerializer;
use EonX\EasyBatch\Transformers\BatchItemTransformer;
use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractTestCase extends TestCase
{
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

    protected function getBatchItemFactory(): BatchItemFactoryInterface
    {
        return $this->batchItemFactory = $this->batchItemFactory ??
            new BatchItemFactory(
                new BatchItemTransformer(new MessageSerializer())
            );
    }

    protected function getIdStrategy(): BatchObjectIdStrategyInterface
    {
        if ($this->batchObjectIdStrategy !== null) {
            return $this->batchObjectIdStrategy;
        }

        $strategy = new UuidV4Strategy((new RandomGenerator())
            ->setUuidV4Generator(new RamseyUuidV4Generator()));

        return $this->batchObjectIdStrategy = $strategy;
    }
}
