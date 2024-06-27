<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Exception;

use InvalidArgumentException;

abstract class AbstractEasyPaginationException extends InvalidArgumentException implements
    EasyPaginationExceptionInterface
{
    // No bod needed
}
