<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Stub\JobHandler;

use Illuminate\Contracts\Queue\Job;
use RuntimeException;

final class JobHandlerStub
{
    public function fire(Job $job, array $data): void
    {
        if (isset($data['exception'])) {
            throw new RuntimeException((string)$data['exception']);
        }
    }
}
