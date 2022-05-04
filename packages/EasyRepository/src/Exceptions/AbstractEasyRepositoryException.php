<?php

declare(strict_types=1);

namespace EonX\EasyRepository\Exceptions;

use EonX\EasyRepository\Interfaces\EasyRepositoryExceptionInterface;

abstract class AbstractEasyRepositoryException extends \RuntimeException implements EasyRepositoryExceptionInterface
{
    // No body needed.
}
