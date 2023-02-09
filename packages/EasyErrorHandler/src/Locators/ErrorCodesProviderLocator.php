<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Locators;

use EonX\EasyErrorHandler\Interfaces\ErrorCodesProviderInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorCodesProviderLocatorInterface;

final class ErrorCodesProviderLocator implements ErrorCodesProviderLocatorInterface
{
    /**
     * @param array<string, \EonX\EasyErrorHandler\Interfaces\ErrorCodesProviderInterface> $errorCodesProviders
     */
    public function __construct(private readonly array $errorCodesProviders)
    {
    }

    public function locate(string $errorCodesSource): ErrorCodesProviderInterface
    {
        return $this->errorCodesProviders[$errorCodesSource];
    }
}
