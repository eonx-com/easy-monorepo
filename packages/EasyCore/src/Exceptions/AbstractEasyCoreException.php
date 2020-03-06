<?php
declare(strict_types=1);

namespace EonX\EasyCore\Exceptions;

use EonX\EasyCore\Interfaces\EasyCoreExceptionInterface;

abstract class AbstractEasyCoreException extends \Exception implements EasyCoreExceptionInterface
{
    // No body needed.
}
