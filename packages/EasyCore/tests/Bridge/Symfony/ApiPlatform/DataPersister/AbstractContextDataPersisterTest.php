<?php
declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\ApiPlatform\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use EonX\EasyCore\Tests\Bridge\Symfony\Stubs\ContextDataPersisterStub;
use EonX\EasyCore\Tests\Bridge\Symfony\Stubs\EntityStub;

final class AbstractContextDataPersisterTest extends AbstractSymfonyTestCase
{
    /**
     * Should persist.
     *
     * @return void
     */
    public function testPersist(): void
    {
        $entity = new EntityStub();
        $context = ['context' => ''];

        $dataPersister = $this->prophesize(ContextAwareDataPersisterInterface::class);
        $dataPersister->persist($entity, $context)
            ->shouldBeCalledOnce()
            ->hasReturnVoid();

        self::assertNull((new ContextDataPersisterStub(
            $dataPersister->reveal()
        ))->persist($entity, $context));
    }

    /**
     * Should remove.
     *
     * @return void
     */
    public function testRemove(): void
    {
        $entity = new EntityStub();
        $context = ['context' => ''];

        $dataPersister = $this->prophesize(ContextAwareDataPersisterInterface::class);
        $dataPersister->remove($entity, $context)
            ->shouldBeCalledOnce()
            ->hasReturnVoid();

        self::assertNull((new ContextDataPersisterStub(
            $dataPersister->reveal()
        ))->remove($entity, $context));
    }

    /**
     * Should support.
     *
     * @return void
     */
    public function testSupports(): void
    {
        self::assertTrue((new ContextDataPersisterStub(
            $this->prophesize(ContextAwareDataPersisterInterface::class)->reveal()
        ))->supports(new EntityStub(), []));
    }
}
