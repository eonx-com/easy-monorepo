<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Doctrine\Coroutine\PDO;

use co;
use OpenSwoole\Core\Coroutine\Pool\ClientPool;
use OpenSwoole\Coroutine;
use ReflectionClass;
use Throwable;
use UnexpectedValueException;

final class PDOClientPool extends ClientPool
{
    /**
     * @var \ReflectionClass<\OpenSwoole\Core\Coroutine\Pool\ClientPool>|null
     */
    private ?ReflectionClass $parentReflection = null;

    public function __construct(
        PDOClientFactory $factory,
        PDOClientConfig $config,
        int $size,
        bool $heartbeat,
        private readonly float $maxIdleTime,
    ) {
        parent::__construct($factory, $config, $size, $heartbeat);
    }

    /**
     * @throws \ReflectionException
     */
    protected function heartbeat(): void
    {
        $poolProperty = $this->getParentReflectionClass()
            ->getProperty('pool');

        /** @var \OpenSwoole\Coroutine\Channel $pool */
        $pool = $poolProperty->getValue($this);

        Coroutine::create(function () use ($pool): void {
            while (true) {
                co::sleep(3);

                if ($pool->isEmpty()) {
                    continue;
                }

                /** @var \EonX\EasySwoole\Bridge\Doctrine\Coroutine\PDO\PDOClient $client */
                $client = $this->get();

                $shouldClose = $client->getLastUsedTime() !== null
                    && $client->getLastUsedTime() + $this->maxIdleTime < \microtime(true);

                if ($shouldClose) {
                    unset($client);

                    continue;
                }

                $client->heartbeat();
                $this->put($client);
            }
        });
    }

    /**
     * @throws \ReflectionException
     * @throws \Throwable
     */
    protected function make(): void
    {
        $numProperty = $this->getParentReflectionClass()
            ->getProperty('num');

        $originalNumValue = $numProperty->getValue($this);

        try {
            parent::make();
        } catch (Throwable $throwable) {
            $newNumValue = $numProperty->getValue($this);

            // If num value was increased and not decreased again because of the exception
            // then decrease it to keep the pool state correct, looks like a bug in openswoole
            if ($newNumValue > 0 && $newNumValue > $originalNumValue) {
                $numProperty->setValue($newNumValue - 1);
            }

            throw $throwable;
        }
    }

    /**
     * @return \ReflectionClass<\OpenSwoole\Core\Coroutine\Pool\ClientPool>
     */
    private function getParentReflectionClass(): ReflectionClass
    {
        if ($this->parentReflection !== null) {
            return $this->parentReflection;
        }

        $reflectionParentClass = (new ReflectionClass($this))->getParentClass();

        if ($reflectionParentClass === false) {
            throw new UnexpectedValueException('Unable to get the parent class.');
        }

        return $this->parentReflection = $reflectionParentClass;
    }
}
