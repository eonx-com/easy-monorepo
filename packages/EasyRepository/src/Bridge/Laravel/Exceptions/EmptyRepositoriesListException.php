<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Bridge\Laravel\Exceptions;

use Exception;
use EonX\EasyRepository\Interfaces\EasyRepositoryExceptionInterface;

final class EmptyRepositoriesListException extends Exception implements EasyRepositoryExceptionInterface
{
    // No body needed.
}


