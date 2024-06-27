<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

interface SeverityAwareExceptionInterface
{
    public const SEVERITY_ERROR = 'error';

    public const SEVERITY_INFO = 'info';

    public const SEVERITY_WARNING = 'warning';

    public function getSeverity(): string;
}
