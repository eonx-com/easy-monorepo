<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Stack;

use EonX\EasyWebhook\Common\Middleware\MiddlewareInterface;

interface StackInterface
{
    public function getCurrentIndex(): int;

    public function next(): MiddlewareInterface;

    public function rewind(): void;

    public function rewindTo(int $index): void;
}
