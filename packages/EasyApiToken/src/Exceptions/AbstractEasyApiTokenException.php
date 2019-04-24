<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Exceptions;

use StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenExceptionInterface;

abstract class AbstractEasyApiTokenException extends \Exception implements EasyApiTokenExceptionInterface
{
    // No body needed.
}

\class_alias(
    AbstractEasyApiTokenException::class,
    'LoyaltyCorp\EasyApiToken\Exceptions\AbstractEasyApiTokenException',
    false
);
