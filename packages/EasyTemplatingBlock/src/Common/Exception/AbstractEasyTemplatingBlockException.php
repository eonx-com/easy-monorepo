<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Common\Exception;

use EonX\EasyTemplatingBlock\Common\Exception\EasyTemplatingBlockExceptionInterface as ExceptionInterface;
use RuntimeException;

abstract class AbstractEasyTemplatingBlockException extends RuntimeException implements ExceptionInterface
{
    // No body needed
}
