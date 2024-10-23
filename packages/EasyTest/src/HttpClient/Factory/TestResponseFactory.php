<?php
declare(strict_types=1);

namespace EonX\EasyTest\HttpClient\Factory;

use Closure;
use EonX\EasyTest\HttpClient\Response\AbstractTestResponse;
use Iterator;
use RuntimeException;
use Throwable;
use UnexpectedValueException;

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

    /**
     * @var array<\EonX\EasyTest\HttpClient\Response\AbstractTestResponse>
     */
    private static array $responsesWithIgnoreOrder = [];

    public static function addResponse(AbstractTestResponse $response): void
    {
        if ($response->isIgnoreOrder()) {
            self::$responsesWithIgnoreOrder[] = $response;

            return;
        }
        self::$responses[] = $response;
    }

    public static function areAllResponsesUsed(): bool
    {
        if (self::getException() !== null) {
            throw self::getException();
        }

        return \count(self::$responses) === 0 && \count(self::$responsesWithIgnoreOrder) === 0;
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

        if (\count(self::$responses) === 0 && \count(self::$responsesWithIgnoreOrder) === 0) {
            return static function (string $method, string $url, ?array $options = null): void {
                TestResponseFactory::throwException(new RuntimeException(\sprintf(
                    'You should add a response to the TestResponseFactory for the request: %s %s',
                    $method,
                    $url
                )));
            };
        }

        $responsesWithIgnoreOrder = self::$responsesWithIgnoreOrder;

        $factory = $this;

        return static function ($method, $url, $options) use ($factory, $responsesWithIgnoreOrder) {
            foreach ($responsesWithIgnoreOrder as $response) {
                if ($response->isUrlMatched($url)) {
                    $factory->unsetResponse($response);

                    return $response($method, $url, $options);
                }
            }

            $response = $factory->shiftResponse();

            return $response($method, $url, $options);
        };
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

    public function shiftResponse(): AbstractTestResponse
    {
        if (\count(self::$responses) === 0) {
            throw new UnexpectedValueException('Responses array is empty');
        }

        return \array_shift(self::$responses);
    }

    public function unsetResponse(AbstractTestResponse $response): void
    {
        $index = (int)\array_search($response, self::$responsesWithIgnoreOrder, true);
        unset(self::$responsesWithIgnoreOrder[$index]);
    }

    public function valid(): bool
    {
        return true;
    }
}
