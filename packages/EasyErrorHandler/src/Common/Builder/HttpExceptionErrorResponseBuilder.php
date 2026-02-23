<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Builder;

use EonX\EasyUtils\Common\Enum\HttpStatusCode;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class HttpExceptionErrorResponseBuilder extends AbstractErrorResponseBuilder
{
    private const string KEY_MESSAGE = 'message';

    private readonly array $keys;

    public function __construct(
        ?array $keys = null,
        ?int $priority = null,
    ) {
        $this->keys = $keys ?? [];

        parent::__construct($priority);
    }

    public function buildData(Throwable $throwable, array $data): array
    {
        if ($throwable instanceof HttpExceptionInterface) {
            $key = $this->keys[self::KEY_MESSAGE] ?? self::KEY_MESSAGE;
            $data[$key] = $throwable->getMessage();
        }

        return parent::buildData($throwable, $data);
    }

    public function buildStatusCode(Throwable $throwable, ?HttpStatusCode $statusCode = null): ?HttpStatusCode
    {
        if ($throwable instanceof HttpExceptionInterface) {
            $statusCode = HttpStatusCode::from($throwable->getStatusCode());
        }

        return parent::buildStatusCode($throwable, $statusCode);
    }
}
