<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Bridge\Laravel\Exceptions;

use EonX\EasyRepository\Interfaces\EasyRepositoryExceptionInterface;
use Exception;

final class EmptyRepositoriesListException extends Exception implements EasyRepositoryExceptionInterface
{
    // No body needed
}
