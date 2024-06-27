<?php
declare(strict_types=1);

namespace EonX\EasyTest\HttpClient\Factory;

use Closure;
use EonX\EasyTest\HttpClient\Response\AbstractTestResponse;
use Iterator;
use RuntimeException;
use Throwable;

/**
 * @implements \Iterator<int, \EonX\EasyTest\HttpClient\Response\AbstractTestResponse>
 */
final class TestResponseFactory implements Iterator
{
    private static ?Throwable $exception = null;

    /**
     * @var array<\EonX\EasyTest\HttpClient\Response\AbstractTestResponse>
     */
    private static array $responses = [];

    public static function addResponse(AbstractTestResponse $request): void
    {
        self::$responses[] = $request;
    }

    public static function areAllResponsesUsed(): bool
    {
        if (self::getException() !== null) {
            throw self::getException();
        }

        return \count(self::$responses) === 0;
    }

    public static function getException(): ?Throwable
    {
        return self::$exception;
    }

    public static function reset(): void
    {
        self::$responses = [];
        self::$exception = null;
    }

    public static function throwException(Throwable $exception): void
    {
        if (self::getException() !== null) {
            throw self::getException();
        }

        self::$exception = $exception;

        throw $exception;
    }

    public function current(): AbstractTestResponse|Closure
    {
        if (self::getException() !== null) {
            throw self::getException();
        }

        if (\count(self::$responses) === 0) {
            return static function (string $method, string $url, ?array $options = null): void {
                TestResponseFactory::throwException(new RuntimeException(\sprintf(
                    'You should add a response to the TestResponseFactory for the request: %s %s',
                    $method,
                    $url
                )));
            };
        }

        return \array_shift(self::$responses);
    }

    public function key(): int
    {
        throw new RuntimeException('Method is not supported');
    }

    public function next(): void
    {
    }

    public function rewind(): void
    {
        throw new RuntimeException('Method is not supported');
    }

    public function valid(): bool
    {
        return true;
    }
}
