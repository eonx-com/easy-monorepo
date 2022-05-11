<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Providers;

use EonX\EasyErrorHandler\Bridge\Symfony\Interfaces\ErrorCodes\ErrorCodesProviderInterface;
use EonX\EasyErrorHandler\Exceptions\ErrorCodesProviderException;

final class ErrorCodesProvider implements ErrorCodesProviderInterface
{
    /**
     * @return array<string, int>
     *
     * @throws \EonX\EasyErrorHandler\Exceptions\ErrorCodesProviderException
     */
    public function provide(): array
    {
        throw new ErrorCodesProviderException('exceptions.error_codes_provider.not_configured');
    }
}
