<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Repositories;

use EonX\EasyBatch\Interfaces\BatchItemFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Objects\MessageDecorator;
use EonX\EasyBatch\Repositories\BatchItemRepository;
use EonX\EasyBatch\Tests\AbstractRepositoriesTestCase;
use EonX\EasyPagination\Data\StartSizeData;
use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\DelayStamp;

final class BatchItemRepositoryTest extends AbstractRepositoriesTestCase
{
    public function providerTestFindForDispatch(): iterable
    {
        yield 'Fetch only batchItems for batch and no dependency' => [
            static function (BatchItemFactoryInterface $factory, BatchItemRepositoryInterface $repo): void {
                $batchItem1 = $factory->create('batch-id');
                $batchItem1->setName('right-one');

                $batchItem2 = $factory->create('another-batch-id');
                $batchItem3 = $factory->create('batch-id')->setDependsOnName('dependency');

                $repo->save($batchItem1);
                $repo->save($batchItem2);
                $repo->save($batchItem3);
            },
            static function (LengthAwarePaginatorInterface $paginator): void {
                self::assertCount(1, $paginator->getItems());
                self::assertEquals('right-one', $paginator->getItems()[0]->getName());
            },
        ];

        yield 'Fetch only batchItems for batch and given dependency' => [
            static function (BatchItemFactoryInterface $factory, BatchItemRepositoryInterface $repo): void {
                $batchItem1 = $factory->create('batch-id');
                $batchItem1->setName('right-one');
                $batchItem1->setDependsOnName('dependency');

                $batchItem2 = $factory->create('another-batch-id');
                $batchItem3 = $factory->create('batch-id');
                $batchItem3->setName('dependency');

                $repo->save($batchItem1);
                $repo->save($batchItem2);
                $repo->save($batchItem3);
            },
            static function (LengthAwarePaginatorInterface $paginator): void {
                self::assertCount(1, $paginator->getItems());
                self::assertEquals('right-one', $paginator->getItems()[0]->getName());
            },
            'dependency',
        ];
    }

    /**
     * @dataProvider providerTestFindForDispatch
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function testFindForDispatch(callable $setup, callable $test, ?string $dependsOnName = null): void
    {
        $factory = $this->getBatchItemFactory();

        $repo = new BatchItemRepository(
            $factory,
            $this->getIdStrategy(),
            $this->getDoctrineDbalConnection(),
            BatchItemRepository::DEFAULT_TABLE
        );

        \call_user_func($setup, $factory, $repo);

        $paginator = $repo->findForDispatch(new StartSizeData(1, 15), 'batch-id', $dependsOnName);

        \call_user_func($test, $paginator);
    }

    public function testShowcase(): void
    {
        $batchFactory = $this->getBatchFactory();

        $itemsProvider = static function (): iterable {
            $message1 = MessageDecorator::wrap(new \stdClass());
            $message1->setName('BatchItem1');

            $message2Envelope = Envelope::wrap(new \stdClass(), [new DelayStamp(6000)]);

            $message2 = MessageDecorator::wrap($message2Envelope);
            $message2->setDependsOn('BatchItem1');

            yield $message1;
            yield $message2;
        };

        $batch = $batchFactory->createFromCallable($itemsProvider);
        $batch->setName('Master Batch');
    }
}
