<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Exceptions;

use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenExceptionInterface;

abstract class AbstractEasyApiTokenFactoryException extends \Exception implements EasyApiTokenExceptionInterface
{
    // No body needed.
}

\class_alias(
    AbstractEasyApiTokenFactoryException::class,
    'StepTheFkUp\EasyApiToken\Exceptions\AbstractEasyApiTokenFactoryException',
    false
);
