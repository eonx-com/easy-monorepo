<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\ApiPlatform\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use EonX\EasyCore\Tests\Bridge\Symfony\Stubs\DataPersisterStub;
use EonX\EasyCore\Tests\Bridge\Symfony\Stubs\EntityStub;

final class AbstractDataPersisterTest extends AbstractSymfonyTestCase
{
    public function testPersist(): void
    {
        $entity = new EntityStub();

        $dataPersister = $this->prophesize(DataPersisterInterface::class);
        $dataPersister->persist($entity)
            ->shouldBeCalledOnce()
            ->hasReturnVoid();

        $dataPersister = $dataPersister->reveal();

        /** @var \ApiPlatform\Core\DataPersister\DataPersisterInterface $dataPersister */

        self::assertNull((new DataPersisterStub($dataPersister))->persist($entity));
    }

    public function testRemove(): void
    {
        $entity = new EntityStub();

        $dataPersister = $this->prophesize(DataPersisterInterface::class);
        $dataPersister->remove($entity)
            ->shouldBeCalledOnce()
            ->hasReturnVoid();

        $dataPersister = $dataPersister->reveal();

        /** @var \ApiPlatform\Core\DataPersister\DataPersisterInterface $dataPersister */

        self::assertNull((new DataPersisterStub($dataPersister))->remove($entity));
    }

    public function testSupports(): void
    {
        $dataPersister = $this->prophesize(DataPersisterInterface::class)->reveal();

        /** @var \ApiPlatform\Core\DataPersister\DataPersisterInterface $dataPersister */

        self::assertTrue((new DataPersisterStub($dataPersister))->supports(new EntityStub()));
    }
}
