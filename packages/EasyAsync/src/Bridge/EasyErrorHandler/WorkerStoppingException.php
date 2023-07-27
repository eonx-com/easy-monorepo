<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\EasyErrorHandler;

use EonX\EasyErrorHandler\Exceptions\ErrorException;

final class WorkerStoppingException extends ErrorException
{
    // No body needed
}
