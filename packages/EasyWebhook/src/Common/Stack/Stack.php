<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Stack;

use EonX\EasyUtils\Common\Helper\CollectorHelper;
use EonX\EasyWebhook\Common\Exception\InvalidStackIndexException;
use EonX\EasyWebhook\Common\Exception\NoNextMiddlewareException;
use EonX\EasyWebhook\Common\Middleware\MiddlewareInterface;

final class Stack implements StackInterface
{
    private int $index = 0;

    /**
     * @var \EonX\EasyWebhook\Common\Middleware\MiddlewareInterface[]
     */
    private array $middleware;

    public function __construct(iterable $middleware)
    {
        $this->middleware = CollectorHelper::orderLowerPriorityFirstAsArray(
            CollectorHelper::filterByClass($middleware, MiddlewareInterface::class)
        );
    }

    public function getCurrentIndex(): int
    {
        return $this->index;
    }

    /**
     * @return \EonX\EasyWebhook\Common\Middleware\MiddlewareInterface[]
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function next(): MiddlewareInterface
    {
        $next = $this->middleware[$this->index] ?? null;

        // This shouldn't happen as we must make sure SendWebhookMiddleware is always the last one
        if ($next === null) {
            throw new NoNextMiddlewareException(\sprintf('No next middleware for index %d', $this->index));
        }

        ++$this->index;

        return $next;
    }

    public function rewind(): void
    {
        $this->index = 0;
    }

    public function rewindTo(int $index): void
    {
        if ($index < 0) {
            throw new InvalidStackIndexException(\sprintf('Stack index must be positive, %s given', $index));
        }

        $this->index = $index;
    }
}
