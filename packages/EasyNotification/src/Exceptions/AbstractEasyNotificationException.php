<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Exceptions;

use EonX\EasyNotification\Interfaces\EasyNotificationExceptionInterface;

abstract class AbstractEasyNotificationException extends \RuntimeException implements EasyNotificationExceptionInterface
{
    // No body needed.
}
