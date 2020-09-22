<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Interfaces;

interface RequestIdServiceInterface
{
    public function getCorrelationId(): string;

    public function getRequestId(): string;
}
