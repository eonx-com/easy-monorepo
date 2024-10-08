<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stub\Stack;

use EonX\EasyWebhook\Common\Middleware\MiddlewareInterface;
use EonX\EasyWebhook\Common\Stack\StackInterface;

final class StackStub implements StackInterface
{
    /**
     * @var int[]
     */
    private array $calls = [];

    public function __construct(
        private readonly StackInterface $decorated,
    ) {
    }

    /**
     * @return int[]
     */
    public function getCalls(): array
    {
        return $this->calls;
    }

    public function getCurrentIndex(): int
    {
        return $this->decorated->getCurrentIndex();
    }

    public function next(): MiddlewareInterface
    {
        $next = $this->decorated->next();
        $class = $next::class;

        if (isset($this->calls[$class]) === false) {
            $this->calls[$class] = 0;
        }

        $this->calls[$class]++;

        return $next;
    }

    public function rewind(): void
    {
        $this->decorated->rewind();
    }

    public function rewindTo(int $index): void
    {
        $this->decorated->rewindTo($index);
    }
}
