<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

interface ErrorCodesProviderInterface
{
    /**
     * @return mixed[]
     */
    public function provide(): array;
}
