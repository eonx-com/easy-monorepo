<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Exceptions;

use EonX\EasyDoctrine\Interfaces\EasyDoctrineExceptionInterface;

abstract class AbstractEasyDoctrineException extends \RuntimeException implements EasyDoctrineExceptionInterface
{
    // No body needed.
}
