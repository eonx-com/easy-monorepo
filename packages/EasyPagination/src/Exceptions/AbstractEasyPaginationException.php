<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Exceptions;

use EonX\EasyPagination\Interfaces\EasyPaginationExceptionInterface;

abstract class AbstractEasyPaginationException extends \InvalidArgumentException implements EasyPaginationExceptionInterface
{
    // No bod needed.
}
