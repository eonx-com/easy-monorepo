<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Builders;

use EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderInterface;
use Throwable;

abstract class AbstractErrorResponseBuilder implements ErrorResponseBuilderInterface
{
    /**
     * @var int
     */
    private $priority;

    public function __construct(?int $priority = null)
    {
        $this->priority = $priority ?? 0;
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
     * @param mixed[] $headers
     *
     * @return mixed[]
     */
    public function buildHeaders(Throwable $throwable, array $headers): array
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
