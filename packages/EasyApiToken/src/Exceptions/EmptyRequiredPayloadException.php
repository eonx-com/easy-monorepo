<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Exceptions;

final class EmptyRequiredPayloadException extends AbstractEasyApiTokenException
{
    // No body needed.
}

\class_alias(
    EmptyRequiredPayloadException::class,
    'StepTheFkUp\EasyApiToken\Exceptions\EmptyRequiredPayloadException',
    false
);
