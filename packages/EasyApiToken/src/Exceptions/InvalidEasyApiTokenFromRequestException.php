<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Exceptions;

final class InvalidEasyApiTokenFromRequestException extends AbstractEasyApiTokenException
{
    // No body needed.
}

\class_alias(
    InvalidEasyApiTokenFromRequestException::class,
    'StepTheFkUp\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException',
    false
);
