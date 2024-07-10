<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\ValueObject;

use EonX\EasyUtils\Common\Enum\HttpStatusCode;

interface ErrorResponseDataInterface
{
    public function getHeaders(): array;

    public function getRawData(): array;

    public function getStatusCode(): HttpStatusCode;
}
