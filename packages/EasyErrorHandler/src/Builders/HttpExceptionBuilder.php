<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Builders;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class HttpExceptionBuilder extends AbstractErrorResponseBuilder
{
    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public function buildData(Throwable $throwable, array $data): array
    {
        if ($throwable instanceof HttpExceptionInterface) {
            $data['message'] = $throwable->getMessage();
        }

        return parent::buildData($throwable, $data);
    }

    public function buildStatusCode(Throwable $throwable, ?int $statusCode = null): ?int
    {
        if ($throwable instanceof HttpExceptionInterface) {
            $statusCode = $throwable->getStatusCode();
        }

        return parent::buildStatusCode($throwable, $statusCode);
    }
}
