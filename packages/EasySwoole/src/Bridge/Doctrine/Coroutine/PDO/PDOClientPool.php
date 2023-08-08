<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Doctrine\Coroutine\PDO;

use co;
use OpenSwoole\Core\Coroutine\Pool\ClientPool;
use OpenSwoole\Coroutine;
use ReflectionClass;
use UnexpectedValueException;

final class PDOClientPool extends ClientPool
{
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
        $reflectionParentClass = (new ReflectionClass($this))->getParentClass();

        if ($reflectionParentClass === false) {
            throw new UnexpectedValueException('Unable to get the parent class.');
        }

        $poolProperty = $reflectionParentClass->getProperty('pool');

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
}
