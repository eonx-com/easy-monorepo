<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Laravel\Queue;

use EonX\EasyAsync\Interfaces\ShouldKillWorkerExceptionInterface;
use Illuminate\Queue\Events\JobFailed;

final class ShouldKillWorkerListener extends AbstractQueueListener
{
    public function handle(JobFailed $event): void
    {
        if ($event->exception instanceof ShouldKillWorkerExceptionInterface) {
            $this->killWorker($event->exception);
        }
    }
}
