<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

use Throwable;

interface ErrorResponseBuilderInterface
{
    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public function buildData(Throwable $throwable, array $data): array;

    /**
     * @param null|mixed[] $headers
     *
     * @return null|mixed[]
     */
    public function buildHeaders(Throwable $throwable, ?array $headers = null): ?array;

    public function buildStatusCode(Throwable $throwable, ?int $statusCode = null): ?int;

    public function getPriority(): int;
}
