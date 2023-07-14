<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stubs;

use EonX\EasyWebhook\Interfaces\MiddlewareInterface;
use EonX\EasyWebhook\Interfaces\StackInterface;
use Throwable;

final class StackThrowStub implements StackInterface
{
    private int $index = 0;

    public function __construct(
        private Throwable $throwable,
    ) {
    }

    public function getCurrentIndex(): int
    {
        return $this->index;
    }

    public function next(): MiddlewareInterface
    {
        throw $this->throwable;
    }

    public function rewind(): void
    {
        $this->index = 0;
    }

    public function rewindTo(int $index): void
    {
        $this->index = $index;
    }
}
