<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces;

interface StackInterface
{
    public function next(): MiddlewareInterface;

    public function rewind(): void;
}
