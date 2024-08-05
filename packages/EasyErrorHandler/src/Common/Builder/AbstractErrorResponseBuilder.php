<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Builder;

use EonX\EasyUtils\Common\Enum\HttpStatusCode;
use EonX\EasyUtils\Common\Helper\HasPriorityTrait;
use Throwable;

abstract class AbstractErrorResponseBuilder implements ErrorResponseBuilderInterface
{
    use HasPriorityTrait;

    public function __construct(?int $priority = null)
    {
        $this->doSetPriority($priority);
    }

    public function buildData(Throwable $throwable, array $data): array
    {
        return $data;
    }

    public function buildHeaders(Throwable $throwable, ?array $headers = null): ?array
    {
        return $headers;
    }

    public function buildStatusCode(Throwable $throwable, ?HttpStatusCode $statusCode = null): ?HttpStatusCode
    {
        return $statusCode;
    }
}
