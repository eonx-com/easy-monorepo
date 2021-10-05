<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Bridge\Symfony\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyDoctrine\Bridge\Symfony\EntityManagerResolver;
use EonX\EasyDoctrine\Tests\Bridge\Symfony\AbstractSymfonyTestCase;

final class EntityManagerResolverTest extends AbstractSymfonyTestCase
{
    public function testGetManagerSucceeds(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class)->reveal();
        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManager()
            ->willReturn($entityManager);
        /** @var \Doctrine\Persistence\ManagerRegistry $managerRegistryReveal */
        $managerRegistryReveal = $managerRegistry->reveal();
        $managerResolver = new EntityManagerResolver($managerRegistryReveal);

        $result = $managerResolver->getManager();

        self::assertSame($entityManager, $result);
    }
}
