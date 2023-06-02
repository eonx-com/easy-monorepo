<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Builders;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class HttpExceptionErrorResponseBuilder extends AbstractErrorResponseBuilder
{
    private const KEY_MESSAGE = 'message';

    /**
     * @var mixed[]
     */
    private readonly array $keys;

    /**
     * @param null|mixed[] $keys
     */
    public function __construct(
        ?array $keys = null,
        ?int $priority = null,
    ) {
        $this->keys = $keys ?? [];

        parent::__construct($priority);
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public function buildData(Throwable $throwable, array $data): array
    {
        if ($throwable instanceof HttpExceptionInterface) {
            $key = $this->keys[self::KEY_MESSAGE] ?? self::KEY_MESSAGE;
            $data[$key] = $throwable->getMessage();
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
