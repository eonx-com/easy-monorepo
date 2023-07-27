<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Generators\Exceptions;

use EonX\EasyErrorHandler\Exceptions\ValidationException;
use Throwable;

final class ValidationFailedException extends ValidationException
{
    public function __construct(array $errors, ?string $message = null, ?int $code = null, ?Throwable $previous = null)
    {
        $message = \sprintf('%s. %s', $message ?? '', $this->getErrorsToString($errors));

        $this->setErrors($errors);

        parent::__construct($message, $code ?? 0, $previous);
    }

    /**
     * Get validation errors as string representation.
     */
    public function getErrorsToString(?array $errors = null): string
    {
        $pattern = '[attribute => %s, value => %s, rule => %s]';
        $errorsToString = '';

        foreach ($errors ?? $this->getErrors() as $error) {
            $errorsToString .= \sprintf($pattern, $error['attribute'], $error['value'], $error['rule']);
        }

        return $errorsToString;
    }
}
