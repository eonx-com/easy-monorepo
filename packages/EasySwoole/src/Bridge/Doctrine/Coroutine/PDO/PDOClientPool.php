<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Doctrine\Coroutine\PDO;

use co;
use EonX\EasySwoole\Helpers\OutputHelper;
use EonX\EasySwoole\Runtime\EasySwooleRunner;
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

    public function get(?float $timeout = null): mixed
    {
        OutputHelper::writeln(
            \sprintf(EasySwooleRunner::LOG_PATTERN, 'PDOClientPool::get() - start')
        );

        $parentClassReflection = $this->getParentReflectionClass();
        foreach (['num', 'active'] as $propertyName) {
            $propertyValue = $parentClassReflection->getProperty($propertyName)->getValue($this);

            OutputHelper::writeln(
                \sprintf(EasySwooleRunner::LOG_PATTERN, 'PDOClientPool::get() - ' . $propertyName . ': ' . $propertyValue)
            );
        }

        $return = parent::get($timeout ?? -1);

        OutputHelper::writeln(
            \sprintf(EasySwooleRunner::LOG_PATTERN, 'PDOClientPool::get() - after parent::get()')
        );

        foreach (['num', 'active'] as $propertyName) {
            $propertyValue = $parentClassReflection->getProperty($propertyName)->getValue($this);

            OutputHelper::writeln(
                \sprintf(EasySwooleRunner::LOG_PATTERN, 'PDOClientPool::get() - ' . $propertyName . ': ' . $propertyValue)
            );
        }

        return $return;
    }

    public function put($connection, $isNew = false): void
    {
        OutputHelper::writeln(
            \sprintf(EasySwooleRunner::LOG_PATTERN, 'PDOClientPool::put() - start')
        );

        $parentClassReflection = $this->getParentReflectionClass();
        foreach (['num', 'active'] as $propertyName) {
            $propertyValue = $parentClassReflection->getProperty($propertyName)->getValue($this);

            OutputHelper::writeln(
                \sprintf(EasySwooleRunner::LOG_PATTERN, 'PDOClientPool::put() - ' . $propertyName . ': ' . $propertyValue)
            );
        }

        parent::put($connection, $isNew);

        OutputHelper::writeln(
            \sprintf(EasySwooleRunner::LOG_PATTERN, 'PDOClientPool::put() - after parent::put()')
        );

        foreach (['num', 'active'] as $propertyName) {
            $propertyValue = $parentClassReflection->getProperty($propertyName)->getValue($this);

            OutputHelper::writeln(
                \sprintf(EasySwooleRunner::LOG_PATTERN, 'PDOClientPool::put() - ' . $propertyName . ': ' . $propertyValue)
            );
        }
    }

    /**
     * @throws \ReflectionException
     */
    protected function heartbeat(): void
    {
        $parentClassReflection = $this->getParentReflectionClass();

        /** @var \OpenSwoole\Coroutine\Channel $pool */
        $pool = $parentClassReflection
            ->getProperty('pool')
            ->getValue($this);

        Coroutine::create(function () use ($pool, $parentClassReflection): void {
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
                    OutputHelper::writeln(
                        \sprintf(EasySwooleRunner::LOG_PATTERN, 'PDOClientPool::heartbeat() - closing DB client - before unset')
                    );

                    unset($client);

                    OutputHelper::writeln(
                        \sprintf(EasySwooleRunner::LOG_PATTERN, 'PDOClientPool::heartbeat() - closing DB client - after unset')
                    );

                    // We need to manually update num and active counters otherwise the parent class still believes
                    // the connection is there...
                    foreach (['active', 'num'] as $propertyName) {
                        $propertyReflection = $parentClassReflection->getProperty($propertyName);
                        $propertyValue = $propertyReflection->getValue($this);

                        if ($propertyValue > 0) {
                            $propertyReflection->setValue($this, $propertyValue - 1);
                        }
                    }

                    continue;
                }

                try {
                    OutputHelper::writeln(
                        \sprintf(EasySwooleRunner::LOG_PATTERN, 'PDOClientPool::heartbeat() - before $client->heartbeat()')
                    );

                    $client->heartbeat();

                    OutputHelper::writeln(
                        \sprintf(EasySwooleRunner::LOG_PATTERN, 'PDOClientPool::heartbeat() - after $client->heartbeat()')
                    );

                    $this->put($client);

                    OutputHelper::writeln(
                        \sprintf(EasySwooleRunner::LOG_PATTERN, 'PDOClientPool::heartbeat() - after $this->put()')
                    );
                } catch (\Throwable $throwable) {
                    OutputHelper::writeln(
                        \sprintf(EasySwooleRunner::LOG_PATTERN, 'PDOClientPool::heartbeat() - throwable during heartbeat and put - ' . $throwable->getMessage())
                    );

                    throw $throwable;
                }
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
