<?php
declare(strict_types=1);

namespace EonX\EasyTest\HttpClient\Response;

use EonX\EasyTest\HttpClient\Factory\TestResponseFactory;
use JsonException;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpClient\Exception\TransportException;
use Throwable;
use UnexpectedValueException;

final class StrictTestResponse extends AbstractTestResponse
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request::METHOD_* $method
     * @param array<string, string>|null $headers
     * @param array|string|\Symfony\Component\HttpClient\Exception\TransportException|null $responseData If null, empty array will be used for JSON or empty string otherwise
     * @param array<string, string>|null $responseHeaders
     * @param int|null $responseCode If null, 200 will be used
     */
    public function __construct(
        private readonly string $method,
        string $url,
        protected ?array $query = null,
        private ?array $body = null,
        private ?array $json = null,
        ?array $headers = null,
        array|string|TransportException|null $responseData = null,
        ?array $responseHeaders = null,
        ?int $responseCode = null,
    ) {
        parent::__construct($url, $query, $responseData, $responseHeaders, $responseCode);

        $this->headers = $headers ?? [];
    }

    final protected function checkParameters(string $method, string $url, array $options): void
    {
        $this->checkUrl($url);

        $this->checkMethod($method);

        $this->checkData($options);

        $this->checkHeaders($options);
    }

    private function checkData(array $options): void
    {
        if (isset($options['body'])) {
            $expectedRequestData = null;
            $actualRequestData = [];

            if ($this->body !== null) {
                $expectedRequestData = $this->body;
                \parse_str((string)($options['body']), $actualRequestData);
            }

            if ($this->json !== null) {
                $expectedRequestData = $this->json;

                try {
                    $actualRequestData = \json_decode((string)$options['body'], true, 512, \JSON_THROW_ON_ERROR);
                } catch (JsonException) {
                    TestResponseFactory::throwException(new UnexpectedValueException(\sprintf(
                        'Invalid JSON in request body, probably you should pass `body` instead of `json` for %s',
                        $this->url
                    )));
                }
            }

            try {
                Assert::assertIsArray(
                    $expectedRequestData,
                    \sprintf('You should provide request data for %s', $this->url)
                );
            } catch (Throwable $exception) {
                TestResponseFactory::throwException($exception);
            }

            self::normalizeData($expectedRequestData);
            self::normalizeData($actualRequestData);

            try {
                Assert::assertSame(
                    $expectedRequestData,
                    $actualRequestData,
                    \sprintf('Request body parameters for %s do not match', $this->url)
                );
            } catch (Throwable $exception) {
                TestResponseFactory::throwException($exception);
            }
        }
    }

    private function checkMethod(string $method): void
    {
        try {
            Assert::assertSame(
                $this->method,
                $method,
                \sprintf('Request method for %s does not match', $this->url)
            );
        } catch (Throwable $exception) {
            TestResponseFactory::throwException($exception);
        }
    }
}
