<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Unit\Doctrine\Closer;

use Doctrine\Persistence\ObjectManager;
use EonX\EasyAsync\Doctrine\Closer\ManagersCloser;
use EonX\EasyAsync\Tests\Stub\EntityManagerForSanityStub;
use EonX\EasyAsync\Tests\Stub\ManagerRegistryStub;
use EonX\EasyAsync\Tests\Unit\Doctrine\AbstractStoreTestCase;
use Mockery\LegacyMockInterface;
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
        /** @var \Doctrine\Persistence\ObjectManager $notEmInstance */
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
