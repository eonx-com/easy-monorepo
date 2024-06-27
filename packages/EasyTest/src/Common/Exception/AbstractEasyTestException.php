<?php
declare(strict_types=1);

namespace EonX\EasyTest\Common\Exception;

use Exception;

abstract class AbstractEasyTestException extends Exception implements EasyTestExceptionInterface
{
    // No body needed
}
