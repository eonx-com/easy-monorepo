<?php
declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\ApiPlatform\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use EonX\EasyCore\Tests\Bridge\Symfony\Stubs\DataPersisterStub;
use EonX\EasyCore\Tests\Bridge\Symfony\Stubs\EntityStub;

final class AbstractDataPersisterTest extends AbstractSymfonyTestCase
{
    /**
     * Should persist.
     *
     * @return void
     */
    public function testPersist(): void
    {
        $entity = new EntityStub();

        $dataPersister = $this->prophesize(DataPersisterInterface::class);
        $dataPersister->persist($entity)
            ->shouldBeCalledOnce()
            ->hasReturnVoid();

        self::assertNull((new DataPersisterStub(
            $dataPersister->reveal()
        ))->persist($entity));
    }

    /**
     * Should remove.
     *
     * @return void
     */
    public function testRemove(): void
    {
        $entity = new EntityStub();

        $dataPersister = $this->prophesize(DataPersisterInterface::class);
        $dataPersister->remove($entity)
            ->shouldBeCalledOnce()
            ->hasReturnVoid();

        self::assertNull((new DataPersisterStub(
            $dataPersister->reveal()
        ))->remove($entity));
    }

    /**
     * Should support.
     *
     * @return void
     */
    public function testSupports(): void
    {
        self::assertTrue((new DataPersisterStub(
            $this->prophesize(DataPersisterInterface::class)->reveal()
        ))->supports(new EntityStub()));
    }
}
