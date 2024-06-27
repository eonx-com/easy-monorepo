<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Exception;

use RuntimeException;

abstract class AbstractEasyRandomException extends RuntimeException implements EasyRandomExceptionInterface
{
    // No body needed
}
