<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

interface ErrorCodesProviderLocatorInterface
{
    public const SOURCE_INTERFACE = 'interface';

    public const SOURCE_ENUM = 'enum';

    public function locate(string $errorCodesSource): ErrorCodesProviderInterface;
}
