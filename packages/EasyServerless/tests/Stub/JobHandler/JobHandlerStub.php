<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Stub\JobHandler;

use Illuminate\Contracts\Queue\Job;
use RuntimeException;
use Throwable;

final class JobHandlerStub
{
    private static int $firedCount = 0;

    public static function getFiredCount(): int
    {
        return self::$firedCount;
    }

    public static function reset(): void
    {
        self::$firedCount = 0;
    }

    public function failed(array $data, ?Throwable $throwable): void
    {
        if (isset($data['failedException'])) {
            throw new RuntimeException((string)$data['failedException']);
        }
    }

    public function fire(Job $job, array $data): void
    {
        self::$firedCount++;

        if (isset($data['release'])) {
            $job->release((int)$data['release']);

            return;
        }

        if (($data['fail'] ?? false) === true) {
            $job->fail(new RuntimeException('Failed by the job'));

            return;
        }

        if (isset($data['exception'])) {
            throw new RuntimeException((string)$data['exception']);
        }
    }
}
