<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Reporters;

use EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface;
use EonX\EasyUtils\Traits\HasPriorityTrait;

abstract class AbstractErrorReporter implements ErrorReporterInterface
{
    use HasPriorityTrait;

    public function __construct(
        protected readonly ErrorLogLevelResolverInterface $errorLogLevelResolver,
        ?int $priority = null
    ) {
        $this->doSetPriority($priority);
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
