<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\ErrorCodes\Provider;

interface ErrorCodesProviderInterface
{
    /**
     * @return array<\EonX\EasyErrorHandler\ErrorCodes\ValueObject\ErrorCode>
     */
    public function provide(): array;
}
