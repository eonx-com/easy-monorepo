<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Interfaces;

use Symfony\Component\HttpFoundation\Request;

interface RequestIdServiceInterface
{
    public function getCorrelationId(): string;

    public function getRequestId(): string;

    public function setRequest(Request $request): self;
}
