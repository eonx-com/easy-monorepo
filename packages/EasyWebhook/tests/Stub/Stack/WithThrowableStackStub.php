<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stub\Stack;

use EonX\EasyWebhook\Common\Middleware\MiddlewareInterface;
use EonX\EasyWebhook\Common\Stack\StackInterface;
use Throwable;

final class WithThrowableStackStub implements StackInterface
{
    private int $index = 0;

    public function __construct(
        private readonly Throwable $throwable,
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
