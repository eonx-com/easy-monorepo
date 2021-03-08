<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use EonX\EasyUtils\Traits\HasPriorityTrait;
use EonX\EasyWebhook\Interfaces\MiddlewareInterface;

abstract class AbstractMiddleware implements MiddlewareInterface
{
    use HasPriorityTrait;

    public function __construct(?int $priority = null)
    {
        if ($priority !== null) {
            $this->priority = $priority;
        }
    }
}
