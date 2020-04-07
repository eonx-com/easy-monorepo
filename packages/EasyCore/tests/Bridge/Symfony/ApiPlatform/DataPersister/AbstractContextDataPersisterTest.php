<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\ApiPlatform\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use EonX\EasyCore\Tests\Bridge\Symfony\Stubs\ContextDataPersisterStub;
use EonX\EasyCore\Tests\Bridge\Symfony\Stubs\EntityStub;

final class AbstractContextDataPersisterTest extends AbstractSymfonyTestCase
{
    public function testPersist(): void
    {
        $entity = new EntityStub();
        $context = ['context' => ''];

        $dataPersister = $this->prophesize(ContextAwareDataPersisterInterface::class);
        $dataPersister->persist($entity, $context)
            ->shouldBeCalledOnce()
            ->hasReturnVoid();

        $dataPersister = $dataPersister->reveal();

        /** @var \ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface $dataPersister */

        self::assertNull((new ContextDataPersisterStub($dataPersister))->persist($entity, $context));
    }

    public function testRemove(): void
    {
        $entity = new EntityStub();
        $context = ['context' => ''];

        $dataPersister = $this->prophesize(ContextAwareDataPersisterInterface::class);
        $dataPersister->remove($entity, $context)
            ->shouldBeCalledOnce()
            ->hasReturnVoid();

        $dataPersister = $dataPersister->reveal();

        /** @var \ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface $dataPersister */

        self::assertNull((new ContextDataPersisterStub($dataPersister))->remove($entity, $context));
    }

    public function testSupports(): void
    {
        $dataPersister = $this->prophesize(ContextAwareDataPersisterInterface::class)->reveal();

        /** @var \ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface $dataPersister */

        self::assertTrue((new ContextDataPersisterStub($dataPersister))->supports(new EntityStub(), []));
    }
}
