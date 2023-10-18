<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Traits;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyDoctrine\Tests\AbstractTestCase;
use EonX\EasyDoctrine\Traits\EntityManagerAwareTrait;

final class EntityManagerAwareTraitTest extends AbstractTestCase
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
