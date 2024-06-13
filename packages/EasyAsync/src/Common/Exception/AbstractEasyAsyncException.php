<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Common\Exception;

use Exception;

abstract class AbstractEasyAsyncException extends Exception implements EasyAsyncExceptionInterface
{
    // No body needed
}
