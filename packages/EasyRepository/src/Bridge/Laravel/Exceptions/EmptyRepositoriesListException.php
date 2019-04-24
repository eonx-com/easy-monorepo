<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyRepository\Bridge\Laravel\Exceptions;

use Exception;
use LoyaltyCorp\EasyRepository\Interfaces\EasyRepositoryExceptionInterface;

final class EmptyRepositoriesListException extends Exception implements EasyRepositoryExceptionInterface
{
    // No body needed.
}

\class_alias(
    EmptyRepositoriesListException::class,
    'StepTheFkUp\EasyRepository\Bridge\Laravel\Exceptions\EmptyRepositoriesListException',
    false
);
