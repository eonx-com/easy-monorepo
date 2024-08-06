<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

use EonX\EasyErrorHandler\Common\Enum\ExceptionSeverity;

interface SeverityAwareExceptionInterface
{
    public function getSeverity(): ExceptionSeverity;
}
