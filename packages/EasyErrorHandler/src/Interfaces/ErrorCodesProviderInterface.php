<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

interface ErrorCodesProviderInterface
{
    /**
     * @return array<\EonX\EasyErrorHandler\DataTransferObjects\ErrorCodeDto>
     */
    public function provide(): array;
}
