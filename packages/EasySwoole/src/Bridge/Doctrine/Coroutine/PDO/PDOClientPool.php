<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Doctrine\Coroutine\PDO;

use co;
use OpenSwoole\Coroutine;
use OpenSwoole\Coroutine\Channel;
use Throwable;

final class PDOClientPool
{
    private int $connectionCount = 0;

    private Channel $pool;

    public function __construct(
        private readonly PDOClientFactory $factory,
        private readonly PDOClientConfig $config,
        private readonly int $size,
        bool $heartbeat,
        private readonly float $maxIdleTime,
    ) {
        $this->pool = new Channel($this->size);

        if ($heartbeat) {
            $this->heartbeat();
        }
    }

    public function get(): PDOClient
    {
        $pdo = null;

        // If no available connection in the pool, and not reached maximum size, then create new PDOClient
        if ($this->pool->isEmpty() && $this->connectionCount < $this->size) {
            $pdo = $this->factory::make($this->config);

            // Increment count of connections only after make(), so we are sure we have successfully created
            // the new PDOClient
            $this->connectionCount++;
        }

        // If we just created a new PDOClient return it, otherwise get one from the pool
        return $pdo ?? $this->pool->pop();
    }

    public function put(PDOClient $client): void
    {
        $this->pool->push($client);
    }

    private function heartbeat(): void
    {
        Coroutine::create(function (): void {
            while (true) {
                // Trigger every 3 seconds only to save CPU usage
                co::sleep(3);

                // If pool is empty, then no need to check
                if ($this->pool->isEmpty()) {
                    continue;
                }

                $pdo = $this->get();

                // If PDOClient was never used, trigger fake usage, so it gets recycled in next idle cycle
                if ($pdo->getLastUsedTime() === null) {
                    $pdo->triggerLastUsedTime();

                    $this->put($pdo);

                    continue;
                }

                // If PDOClient was idle for too long, close the connection
                if ($pdo->getLastUsedTime() + $this->maxIdleTime < \microtime(true)) {
                    unset($pdo);

                    // Decrease the pool connections count
                    $this->connectionCount--;

                    continue;
                }

                try {
                    $pdo->heartbeat();
                } catch (Throwable) {
                    // If the PDOClient is compromised, close the connection
                    unset($pdo);

                    // Decrease the pool connections count
                    $this->connectionCount--;

                    continue;
                }

                // Otherwise put PDOClient back in the pool
                $this->put($pdo);
            }
        });
    }
}
