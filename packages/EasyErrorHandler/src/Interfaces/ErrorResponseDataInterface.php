<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

interface ErrorResponseDataInterface
{
    public function getHeaders(): array;

    public function getRawData(): array;

    public function getStatusCode(): int;
}
