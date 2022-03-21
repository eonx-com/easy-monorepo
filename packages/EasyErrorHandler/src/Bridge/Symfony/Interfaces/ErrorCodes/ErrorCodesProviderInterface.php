<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Interfaces\ErrorCodes;

interface ErrorCodesProviderInterface
{
    /**
     * @return array<string, int>
     */
    public function provide(): array;
}
