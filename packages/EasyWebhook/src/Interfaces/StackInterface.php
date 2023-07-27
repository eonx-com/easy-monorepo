<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces;

interface StackInterface
{
    public function getCurrentIndex(): int;

    public function next(): MiddlewareInterface;

    public function rewind(): void;

    public function rewindTo(int $index): void;
}
