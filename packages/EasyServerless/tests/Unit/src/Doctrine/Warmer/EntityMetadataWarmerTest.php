<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Doctrine\Warmer;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Persistence\ObjectManager;
use EonX\EasyServerless\Doctrine\Warmer\EntityMetadataWarmer;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;

final class EntityMetadataWarmerTest extends AbstractUnitTestCase
{
    public function testWarmLoadsAllMetadataOfEachManager(): void
    {
        $defaultMetadataFactory = $this->createMock(ClassMetadataFactory::class);
        $defaultMetadataFactory
            ->expects(self::once())
            ->method('getAllMetadata')
            ->willReturn([]);
        $defaultManager = $this->createStub(ObjectManager::class);
        $defaultManager
            ->method('getMetadataFactory')
            ->willReturn($defaultMetadataFactory);
        $otherMetadataFactory = $this->createMock(ClassMetadataFactory::class);
        $otherMetadataFactory
            ->expects(self::once())
            ->method('getAllMetadata')
            ->willReturn([]);
        $otherManager = $this->createStub(ObjectManager::class);
        $otherManager
            ->method('getMetadataFactory')
            ->willReturn($otherMetadataFactory);
        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry
            ->method('getManagers')
            ->willReturn([
                'default' => $defaultManager,
                'other' => $otherManager,
            ]);
        $sut = new EntityMetadataWarmer($managerRegistry);

        $sut->warm();
    }
}
