<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Bridge\Symfony\Traits;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyDoctrine\Bridge\Symfony\Traits\EntityManagerAwareTrait;
use EonX\EasyDoctrine\Tests\Bridge\Symfony\AbstractSymfonyTestCase;

final class EntityManagerAwareTraitTest extends AbstractSymfonyTestCase
{
    public function testSetEntityManagerSucceeds(): void
    {
        $abstractClass = new class() {
            use EntityManagerAwareTrait;
        };
        /** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
        $entityManager = $this->prophesize(EntityManagerInterface::class)->reveal();

        $abstractClass->setEntityManager($entityManager);

        self::assertSame($entityManager, $this->getPrivatePropertyValue($abstractClass, 'entityManager'));
    }
}
