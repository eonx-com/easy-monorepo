<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Laravel\Queue;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Carbon;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

abstract class AbstractQueueListener
{
    /**
     * @var \Illuminate\Contracts\Cache\Repository
     */
    private $cache;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(Cache $cache, ?LoggerInterface $logger = null)
    {
        $this->cache = $cache;
        $this->logger = $logger ?? new NullLogger();
    }

    protected function killWorker(Throwable $throwable): void
    {
        $this->logger->info(\sprintf('Kill worker because of exception "%s"', \get_class($throwable)));

        $this->cache->forever('illuminate:queue:restart', Carbon::now()->getTimestamp());
    }
}
