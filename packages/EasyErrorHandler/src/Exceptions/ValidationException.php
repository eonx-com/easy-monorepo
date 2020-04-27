<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

use EonX\EasyErrorHandler\Interfaces\ValidationExceptionInterface;

abstract class ValidationException extends BadRequestException implements ValidationExceptionInterface
{
    /**
     * @var mixed[]
     */
    protected $errors = [];

    /**
     * {@inheritdoc}
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * {@inheritdoc}
     */
    public function setErrors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }
}
