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
use EonX\EasyRandom\Generator\RandomGenerator;
use EonX\EasyRandom\Generator\SymfonyUuidV6Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

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
            new RandomGenerator(new SymfonyUuidV6Generator())
        );

        return $this->batchObjectIdStrategy;
    }
}
