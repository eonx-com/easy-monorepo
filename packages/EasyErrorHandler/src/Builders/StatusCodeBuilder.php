<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Builders;

use EonX\EasyErrorHandler\Interfaces\Exceptions\StatusCodeAwareExceptionInterface;
use Throwable;

final class StatusCodeBuilder extends AbstractErrorResponseBuilder
{
    /**
     * @var int[]
     */
    private $exceptionToStatusCode;

    /**
     * @param null|int[] $exceptionToStatusCode
     */
    public function __construct(?array $exceptionToStatusCode = null, ?int $priority = null)
    {
        $this->exceptionToStatusCode = $exceptionToStatusCode ?? [];

        parent::__construct($priority);
    }

    public function buildStatusCode(Throwable $throwable, ?int $statusCode = null): ?int
    {
        if ($statusCode !== null) {
            return $statusCode;
        }

        if ($throwable instanceof StatusCodeAwareExceptionInterface) {
            return $throwable->getStatusCode();
        }

        $exceptionClass = \get_class($throwable);

        foreach ($this->exceptionToStatusCode as $class => $setStatusCode) {
            if (\is_a($exceptionClass, $class, true)) {
                $statusCode = $setStatusCode;

                break;
            }
        }

        return $statusCode;
    }
}
