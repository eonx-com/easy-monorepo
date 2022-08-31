<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Builders;

use EonX\EasyErrorHandler\Interfaces\Exceptions\StatusCodeAwareExceptionInterface;
use Throwable;

final class StatusCodeErrorResponseBuilder extends AbstractErrorResponseBuilder
{
    /**
     * @param int[] $exceptionToStatusCode
     */
    public function __construct(
        private readonly array $exceptionToStatusCode = [],
        ?int $priority = null
    ) {
        parent::__construct($priority);
    }

    public function buildStatusCode(Throwable $throwable, ?int $statusCode = null): ?int
    {
        if ($throwable instanceof StatusCodeAwareExceptionInterface) {
            $statusCode = $throwable->getStatusCode();
        }

        foreach ($this->exceptionToStatusCode as $class => $setStatusCode) {
            if (\is_a($throwable, $class)) {
                $statusCode = $setStatusCode;

                break;
            }
        }

        return parent::buildStatusCode($throwable, $statusCode);
    }
}
