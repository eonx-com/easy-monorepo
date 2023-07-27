<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Exceptions;

use EonX\EasyAsync\Interfaces\EasyAsyncExceptionInterface;
use Exception;

abstract class AbstractEasyAsyncException extends Exception implements EasyAsyncExceptionInterface
{
    // No body needed
}
