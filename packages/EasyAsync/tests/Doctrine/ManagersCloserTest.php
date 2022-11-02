<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Doctrine;

use Doctrine\Persistence\ObjectManager;
use EonX\EasyAsync\Doctrine\ManagersCloser;
use EonX\EasyAsync\Tests\AbstractStoreTestCase;
use EonX\EasyAsync\Tests\Doctrine\Stubs\EntityManagerForSanityStub;
use EonX\EasyAsync\Tests\Doctrine\Stubs\ManagerRegistryStub;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;

final class ManagersCloserTest extends AbstractStoreTestCase
{
    public function testCloseNotEntityManagerInstance(): void
    {
        $registry = new ManagerRegistryStub([
            'default' => new EntityManagerForSanityStub(true),
        ]);

        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = $this->mock(LoggerInterface::class, function (LegacyMockInterface $logger): void {
            $logger->shouldNotReceive('warning');
        });

        (new ManagersCloser($registry, $logger))->close();
    }

    public function testCloseSuccessful(): void
    {
        $notEmInstance = $this->mock(ObjectManager::class);
        $registry = new ManagerRegistryStub([
            'default' => $notEmInstance,
        ]);

        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = $this->mock(LoggerInterface::class, function (LegacyMockInterface $logger): void {
            $logger->shouldReceive('warning')
            ->once();
        });

        (new ManagersCloser($registry, $logger))->close();
    }
}
