<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Builders;

use EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderInterface;
use EonX\EasyUtils\Traits\HasPriorityTrait;
use Throwable;

abstract class AbstractErrorResponseBuilder implements ErrorResponseBuilderInterface
{
    use HasPriorityTrait;

    public function __construct(?int $priority = null)
    {
        $this->doSetPriority($priority);
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public function buildData(Throwable $throwable, array $data): array
    {
        return $data;
    }

    /**
     * @param null|mixed[] $headers
     *
     * @return null|mixed[]
     */
    public function buildHeaders(Throwable $throwable, ?array $headers = null): ?array
    {
        return $headers;
    }

    public function buildStatusCode(Throwable $throwable, ?int $statusCode = null): ?int
    {
        return $statusCode;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
