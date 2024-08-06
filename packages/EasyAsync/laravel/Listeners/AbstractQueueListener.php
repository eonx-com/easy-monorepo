<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Laravel\Listeners;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Carbon;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

abstract class AbstractQueueListener
{
    protected LoggerInterface $logger;

    public function __construct(
        private readonly Cache $cache,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    protected function killWorker(Throwable $throwable): void
    {
        $this->logger->info(\sprintf('Kill worker because of exception "%s"', $throwable::class));

        $this->cache->forever('illuminate:queue:restart', Carbon::now()->getTimestamp());
    }
}
