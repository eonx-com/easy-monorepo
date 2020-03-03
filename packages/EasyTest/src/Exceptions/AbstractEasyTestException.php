<?php
declare(strict_types=1);

namespace EonX\EasyTest\Exceptions;

use EonX\EasyTest\Interfaces\EasyTestExceptionInterface;

abstract class AbstractEasyTestException extends \Exception implements EasyTestExceptionInterface
{
    // No body needed.
}
