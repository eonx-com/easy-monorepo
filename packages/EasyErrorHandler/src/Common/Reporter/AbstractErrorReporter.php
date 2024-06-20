<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Reporter;

use EonX\EasyErrorHandler\Common\Resolver\ErrorLogLevelResolverInterface;
use EonX\EasyUtils\Traits\HasPriorityTrait;

abstract class AbstractErrorReporter implements ErrorReporterInterface
{
    use HasPriorityTrait;

    public function __construct(
        protected readonly ErrorLogLevelResolverInterface $errorLogLevelResolver,
        ?int $priority = null,
    ) {
        $this->doSetPriority($priority);
    }
}
