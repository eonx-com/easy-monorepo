<?php
declare(strict_types=1);

namespace EonX\EasyTest\HttpClient;

use PHPUnit\Framework\Assert;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;
use UnexpectedValueException;

/**
 * @internal  Use `self::arrangeHttpResponse(...) ` instead
 */
final class StrictTestResponse extends AbstractTestResponse
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request::METHOD_* $method
     * @param array<string, string>|null $requestHeaders
     * @param array|string|\Symfony\Component\HttpClient\Exception\TransportException|null $responseData If null, empty array will be used for JSON or empty string otherwise
     * @param array<string, string>|null $responseHeaders
     * @param int|null $responseCode If null, 200 will be used
     */
    public function __construct(
        private readonly string $method,
        string $url,
        protected readonly ?array $queryData = null,
        private readonly ?array $requestData = null,
        private readonly ?array $requestHeaders = null,
        array|string|TransportException|null $responseData = null,
        ?array $responseHeaders = null,
        ?int $responseCode = null,
    ) {
        parent::__construct($url, $queryData, $responseData, $responseHeaders, $responseCode);
    }

    public function __invoke(string $method, string $url, ?array $options = null): MockResponse
    {
        $this->checkUrl($url);

        $this->checkMethod($method);

        $this->checkOptions($options ?? []);

        return $this->createResponse($method, $url, $options ?? []);
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

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function checkOptions(array $options): void
    {
        $headers = $this->requestHeaders ?? [];
        if (isset($headers[self::HEADER_ACCEPT]) === false) {
            $headers[self::HEADER_ACCEPT] = self::CONTENT_TYPE_JSON;
        }

        if ($this->requestData !== null || isset($options['body'])) {
            if (isset($headers[self::HEADER_CONTENT_TYPE]) === false) {
                $headers[self::HEADER_CONTENT_TYPE] = self::CONTENT_TYPE_JSON;
            }

            $actualRequestData = [];
            $requestBody = '';
            $contentTypeHeader = \preg_replace(
                '/^application\/vnd\..*?(\bjson\b).*/',
                'application/json',
                $headers[self::HEADER_CONTENT_TYPE]
            );
            switch ($contentTypeHeader) {
                case self::CONTENT_TYPE_FORM:
                    \parse_str((string)$options['body'], $actualRequestData);
                    $requestBody = \http_build_query($this->requestData ?? []);

                    break;
                case self::CONTENT_TYPE_JSON:
                    $actualRequestData = \json_decode((string)$options['body'], true);
                    $requestBody = (string)\json_encode($this->requestData, JsonResponse::DEFAULT_ENCODING_OPTIONS);

                    break;
                default:
                    TestResponseFactory::throwException(new UnexpectedValueException(\sprintf(
                        'Unsupported Content-Type [%s]',
                        $headers[self::HEADER_CONTENT_TYPE]
                    )));
            }

            $expectedRequestData = $this->requestData;
            $this->sortArray($expectedRequestData);
            $this->sortArray($actualRequestData);

            try {
                Assert::assertSame(
                    $expectedRequestData,
                    $actualRequestData,
                    \sprintf('Request body parameters for %s do not match', $this->url)
                );
            } catch (Throwable $exception) {
                TestResponseFactory::throwException($exception);
            }

            $headers[self::HEADER_CONTENT_LENGTH] = (string)\strlen($requestBody);
        }

        $normalizedHeaders = [];
        foreach ($headers as $header => $value) {
            $normalizedHeaders[\strtolower((string)$header)] = [\sprintf('%s: %s', $header, $value)];
        }

        \ksort($normalizedHeaders);
        \ksort($options['normalized_headers']);

        try {
            Assert::assertSame(
                $normalizedHeaders,
                $options['normalized_headers'],
                \sprintf('Request headers for %s do not match', $this->url)
            );
        } catch (Throwable $exception) {
            TestResponseFactory::throwException($exception);
        }
    }

    private function sortArray(array &$array): void
    {
        foreach ($array as &$value) {
            if (\is_array($value)) {
                $this->sortArray($value);
            }
        }

        $this->sortArray($array);
    }
}
