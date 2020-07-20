<?php

declare(strict_types=1);

namespace EonX\EasyLock;

use EonX\EasyLock\Interfaces\LockServiceInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\PersistingStoreInterface;

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
     * @var \Symfony\Component\Lock\PersistingStoreInterface
     */
    private $store;

    public function __construct(PersistingStoreInterface $store, ?LoggerInterface $logger = null)
    {
        $this->store = $store;
        $this->logger = $logger ?? new NullLogger();
    }

    public function createLock(string $resource, ?float $ttl = null): LockInterface
    {
        return $this->getFactory()->createLock($resource, $ttl ?? 300.0);
    }

    private function getFactory(): LockFactory
    {
        if ($this->factory !== null) {
            return $this->factory;
        }

        $factory = new LockFactory($this->store);
        $factory->setLogger($this->logger);

        return $this->factory = $factory;
    }
}
