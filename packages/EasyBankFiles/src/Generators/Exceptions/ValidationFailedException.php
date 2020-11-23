<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Generators\Exceptions;

use EoneoPay\Utils\Exceptions\ValidationException;
use Throwable;

final class ValidationFailedException extends ValidationException
{
    /**
     * ValidationFailedException constructor.
     *
     * @param mixed[] $errors
     */
    public function __construct(array $errors, ?string $message = null, ?int $code = null, ?Throwable $previous = null)
    {
        $message = \sprintf('%s. %s', $message ?? '', $this->getErrorsToString($errors));

        parent::__construct($message, null, $code, $previous, $errors);
    }

    /**
     * Get Error code.
     */
    public function getErrorCode(): int
    {
        return self::DEFAULT_ERROR_CODE_VALIDATION;
    }

    /**
     * Get Error sub-code.
     */
    public function getErrorSubCode(): int
    {
        return self::DEFAULT_ERROR_SUB_CODE;
    }

    /**
     * Get validation errors as string representation.
     *
     * @param mixed[]|null $errors
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
