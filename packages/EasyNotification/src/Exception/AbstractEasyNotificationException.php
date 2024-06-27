<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Exception;

use RuntimeException;

abstract class AbstractEasyNotificationException extends RuntimeException implements EasyNotificationExceptionInterface
{
    // No body needed
}
