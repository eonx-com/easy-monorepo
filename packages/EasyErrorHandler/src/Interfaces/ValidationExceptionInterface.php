<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

interface ValidationExceptionInterface
{
    /**
     * Returns validation errors.
     *
     * @return mixed[]
     */
    public function getErrors(): array;

    /**
     * Sets validation errors.
     *
     * @param mixed[] $errors
     */
    public function setErrors(array $errors);
}
