<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Laravel\Exceptions;

use EonX\EasyErrorHandler\Common\Exception\ErrorException;

final class WorkerStoppingException extends ErrorException
{
    // No body needed
}
