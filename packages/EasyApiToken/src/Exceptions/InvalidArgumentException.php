<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Exceptions;

final class InvalidArgumentException extends AbstractEasyApiTokenException
{
    // No body needed.
}

\class_alias(
    InvalidArgumentException::class,
    'StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException',
    false
);
