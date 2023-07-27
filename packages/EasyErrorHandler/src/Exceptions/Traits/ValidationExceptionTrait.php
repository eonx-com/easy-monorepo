<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions\Traits;

trait ValidationExceptionTrait
{
    protected array $errors = [];

    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Sets validation errors.
     */
    public function setErrors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }
}
