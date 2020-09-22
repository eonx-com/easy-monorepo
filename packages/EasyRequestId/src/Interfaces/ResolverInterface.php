<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Interfaces;

interface ResolverInterface
{
    /**
     * @var string
     */
    public const DEFAULT_CORRELATION_ID_HEADER = 'X-EONX-CORRELATION-ID';

    /**
     * @var string
     */
    public const DEFAULT_REQUEST_ID_HEADER = 'X-EONX-REQUEST-ID';

    public function getPriority(): int;
}
