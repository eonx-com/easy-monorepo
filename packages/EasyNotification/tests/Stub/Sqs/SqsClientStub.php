<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Stub\Sqs;

use Aws\Sqs\SqsClient;

final class SqsClientStub extends SqsClient
{
    private array $calls = [];

    public function __construct()
    {
        parent::__construct([
            'region' => 'ap-southeast-2',
            'service' => 'sqs',
            'version' => 'latest',
        ]);
    }

    public function getCalls(): array
    {
        return $this->calls;
    }

    public function sendMessage(?array $args = null): void
    {
        $this->calls[] = $args ?? [];
    }
}
