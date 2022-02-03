<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Interfaces;

interface ErrorCodesProviderInterface
{
    /**
     * @return mixed[]
     */
    public function provide(): array;
}
