<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces\Exceptions;

interface SeverityAwareExceptionInterface
{
    /**
     * @var string
     */
    public const SEVERITY_ERROR = 'error';

    /**
     * @var string
     */
    public const SEVERITY_INFO = 'info';

    /**
     * @var string
     */
    public const SEVERITY_WARNING = 'warning';

    public function getSeverity(): string;
}
