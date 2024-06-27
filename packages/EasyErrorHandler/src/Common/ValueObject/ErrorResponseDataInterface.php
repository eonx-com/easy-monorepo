<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\ValueObject;

interface ErrorResponseDataInterface
{
    public function getHeaders(): array;

    public function getRawData(): array;

    public function getStatusCode(): int;
}
