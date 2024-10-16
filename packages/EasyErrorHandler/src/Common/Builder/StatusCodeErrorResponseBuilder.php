<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Builder;

use BackedEnum;
use EonX\EasyErrorHandler\Common\Exception\StatusCodeAwareExceptionInterface;
use EonX\EasyUtils\Common\Enum\HttpStatusCode;
use Throwable;

final class StatusCodeErrorResponseBuilder extends AbstractErrorResponseBuilder
{
    /**
     * @var array<class-string, \EonX\EasyUtils\Common\Enum\HttpStatusCode>
     */
    private readonly array $exceptionToStatusCode;

    /**
     * @param array<class-string, \EonX\EasyUtils\Common\Enum\HttpStatusCode>|null $exceptionToStatusCode
     */
    public function __construct(
        ?array $exceptionToStatusCode = null,
        ?int $priority = null,
    ) {
        $this->exceptionToStatusCode = $exceptionToStatusCode ?? [];

        parent::__construct($priority);
    }

    public function buildStatusCode(Throwable $throwable, ?HttpStatusCode $statusCode = null): ?HttpStatusCode
    {
        if ($throwable instanceof StatusCodeAwareExceptionInterface) {
            $statusCode = $throwable->getStatusCode();
        }

        foreach ($this->exceptionToStatusCode as $class => $setStatusCode) {
            if (\is_a($throwable, $class)) {
                $statusCode = $setStatusCode instanceof HttpStatusCode
                    ? $setStatusCode
                    : HttpStatusCode::from($setStatusCode instanceof BackedEnum
                        ? $setStatusCode->value
                        : $setStatusCode);

                break;
            }
        }

        return parent::buildStatusCode($throwable, $statusCode);
    }
}
