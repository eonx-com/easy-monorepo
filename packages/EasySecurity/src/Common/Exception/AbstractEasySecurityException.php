<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Exception;

use RuntimeException;

abstract class AbstractEasySecurityException extends RuntimeException implements EasySecurityExceptionInterface
{
    // No body needed
}
