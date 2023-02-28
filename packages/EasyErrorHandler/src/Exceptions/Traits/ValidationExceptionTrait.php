<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions\Traits;

trait ValidationExceptionTrait
{
    /**
     * @var mixed[]
     */
    protected $errors = [];

    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Sets validation errors.
     *
     * @param mixed[] $errors
     */
    public function setErrors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }
}
