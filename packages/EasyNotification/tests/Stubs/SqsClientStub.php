<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Stubs;

use Aws\Sqs\SqsClient;

final class SqsClientStub extends SqsClient
{
    /**
     * @var mixed[]
     */
    private array $calls = [];

    public function __construct()
    {
        parent::__construct([
            'region' => 'ap-southeast-2',
            'service' => 'sqs',
            'version' => 'latest',
        ]);
    }

    /**
     * @return mixed[]
     */
    public function getCalls(): array
    {
        return $this->calls;
    }

    /**
     * @param null|mixed[] $args
     */
    public function sendMessage(?array $args = null): void
    {
        $this->calls[] = $args ?? [];
    }
}
