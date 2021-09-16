<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Reporters;

use EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface;

abstract class AbstractErrorReporter implements ErrorReporterInterface
{
    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface
     */
    protected $errorLogLevelResolver;

    /**
     * @var int
     */
    private $priority;

    public function __construct(ErrorLogLevelResolverInterface $errorLogLevelResolver, ?int $priority = null)
    {
        $this->errorLogLevelResolver = $errorLogLevelResolver;
        $this->priority = $priority ?? 0;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
