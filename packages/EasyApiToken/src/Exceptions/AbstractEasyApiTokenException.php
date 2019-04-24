<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Exceptions;

use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenExceptionInterface;

abstract class AbstractEasyApiTokenException extends \Exception implements EasyApiTokenExceptionInterface
{
    // No body needed.
}

\class_alias(
    AbstractEasyApiTokenException::class,
    'StepTheFkUp\EasyApiToken\Exceptions\AbstractEasyApiTokenException',
    false
);
