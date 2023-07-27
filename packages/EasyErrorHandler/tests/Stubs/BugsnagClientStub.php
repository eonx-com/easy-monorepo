<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Stubs;

use Bugsnag\Client;
use Bugsnag\Configuration;

final class BugsnagClientStub extends Client
{
    private array $calls = [];

    public function __construct()
    {
        parent::__construct(new Configuration('my-api-key'));
    }

    public function getCalls(): array
    {
        return $this->calls;
    }

    public function notifyException(mixed $throwable, ?callable $callback = null): void
    {
        $this->calls[] = [$throwable, $callback];
    }
}
