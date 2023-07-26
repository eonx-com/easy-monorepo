<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Interfaces;

use DateTimeInterface;

interface ResponseDataInterface
{
    public function getContent(): string;

    /**
     * @return mixed[]
     */
    public function getHeaders(): array;

    public function getReceivedAt(): DateTimeInterface;

    public function getStatusCode(): int;
}
