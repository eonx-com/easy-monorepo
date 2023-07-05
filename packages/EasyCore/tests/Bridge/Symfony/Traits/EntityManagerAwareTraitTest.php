<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Traits;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyCore\Bridge\Symfony\Traits\EntityManagerAwareTrait;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;

final class EntityManagerAwareTraitTest extends AbstractSymfonyTestCase
{
    public function testSetEntityManagerSucceeds(): void
    {
        $abstractClass = new class() {
            use EntityManagerAwareTrait;
        };
        /** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
        $entityManager = self::mock(EntityManagerInterface::class);

        $abstractClass->setEntityManager($entityManager);

        self::assertSame($entityManager, $this->getPrivatePropertyValue($abstractClass, 'entityManager'));
    }
}
