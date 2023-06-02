<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyCore\Bridge\Symfony\Doctrine\EntityManagerResolver;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use Mockery\MockInterface;

final class EntityManagerResolverTest extends AbstractSymfonyTestCase
{
    public function testGetManagerSucceeds(): void
    {
        $entityManager = $this->mock(EntityManagerInterface::class);
        /** @var \Symfony\Bridge\Doctrine\ManagerRegistry $managerRegistry */
        $managerRegistry = $this->mock(
            ManagerRegistry::class,
            static function (MockInterface $mock) use ($entityManager): void {
                $mock
                    ->shouldReceive('getManager')
                    ->once()
                    ->withNoArgs()
                    ->andReturn($entityManager);
            },
        );
        $managerResolver = new EntityManagerResolver($managerRegistry);

        $result = $managerResolver->getManager();

        self::assertSame($entityManager, $result);
    }
}
