<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

interface ErrorResponseDataInterface
{
    /**
     * @return mixed[]
     */
    public function getHeaders(): array;

    /**
     * @return mixed[]
     */
    public function getRawData(): array;

    public function getStatusCode(): int;
}
