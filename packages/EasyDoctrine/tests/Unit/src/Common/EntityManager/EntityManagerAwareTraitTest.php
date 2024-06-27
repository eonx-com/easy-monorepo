<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Unit\Common\EntityManager;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyDoctrine\Common\EntityManager\EntityManagerAwareTrait;
use EonX\EasyDoctrine\Tests\Unit\AbstractUnitTestCase;

final class EntityManagerAwareTraitTest extends AbstractUnitTestCase
{
    public function testSetEntityManagerSucceeds(): void
    {
        $abstractClass = new class() {
            use EntityManagerAwareTrait;
        };
        /** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
        $entityManager = $this->mock(EntityManagerInterface::class);

        $abstractClass->setEntityManager($entityManager);

        self::assertSame($entityManager, self::getPrivatePropertyValue($abstractClass, 'entityManager'));
    }
}
