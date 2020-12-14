<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;
use Throwable;

interface ErrorResponseBuilderInterface extends HasPriorityInterface
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
}
