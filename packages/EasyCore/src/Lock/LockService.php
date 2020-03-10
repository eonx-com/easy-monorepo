<?php
declare(strict_types=1);

namespace EonX\EasyCore\Lock;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\PersistingStoreInterface;
use Symfony\Component\Lock\Store\PdoStore;

final class LockService implements LockServiceInterface
{
    /**
     * @var \Symfony\Component\Lock\LockFactory
     */
    private $factory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Doctrine\Persistence\ManagerRegistry
     */
    private $registry;

    /**
     * @var \Symfony\Component\Lock\PersistingStoreInterface
     */
    private $store;

    public function __construct(ManagerRegistry $registry, ?LoggerInterface $logger = null)
    {
        $this->registry = $registry;
        $this->logger = $logger ?? new NullLogger();
    }

    public function createLock(string $resource, ?float $ttl = null): LockInterface
    {
        return $this->getFactory()->createLock($resource, $ttl ?? 300.0);
    }

    public function setStore(PersistingStoreInterface $store): LockServiceInterface
    {
        $this->store = $store;

        return $this;
    }

    private function getFactory(): LockFactory
    {
        if ($this->factory !== null) {
            return $this->factory;
        }

        $factory = new LockFactory($this->store ?? new PdoStore($this->registry->getConnection()));
        $factory->setLogger($this->logger);

        return $this->factory = $factory;
    }
}
