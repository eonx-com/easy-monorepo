<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Exceptions;

use EonX\EasyPagination\Interfaces\EasyPaginationExceptionInterface;

final class InvalidPathException extends \InvalidArgumentException implements EasyPaginationExceptionInterface
{
    // No body needed.
}
