<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Warmer;

use Bref\Context\Context;
use EonX\EasyServerless\Aws\Helper\LambdaContextHelper;
use EonX\EasyServerless\Aws\HttpHandler\SymfonyHttpHandler;
use EonX\EasyServerless\SnapStart\Warmer\SnapStartWarmerInterface;
use RuntimeException;

/**
 * Handles a GET request with the SymfonyHttpHandler, so the code and the services of an HTTP request are loaded before
 * the snapshot. Only in the functions whose handler is the SymfonyHttpHandler.
 */
final readonly class SymfonyHttpHandlerWarmer implements SnapStartWarmerInterface
{
    private const REQUEST_ID = 'easy-serverless-snap-start-warm-up';

    private const TIMEOUT_MILLISECONDS = 30_000;

    public function __construct(
        private SymfonyHttpHandler $httpHandler,
        private string $path = '/easy-serverless/health-check',
        private ?string $host = null,
    ) {
    }

    public function warm(): void
    {
        if ($this->isHttpHandlerFunction() === false) {
            return;
        }

        // As in the API Gateway events
        $headers = [
            'Accept' => 'application/json',
            'X-Forwarded-Port' => '443',
            'X-Forwarded-Proto' => 'https',
        ];

        if ($this->host !== null && $this->host !== '') {
            $headers['Host'] = $this->host;
        }

        try {
            $response = $this->httpHandler->handle(
                [
                    'headers' => $headers,
                    'httpMethod' => 'GET',
                    'isBase64Encoded' => false,
                    'path' => $this->path,
                    'requestContext' => [
                        'identity' => [
                            'sourceIp' => '127.0.0.1',
                        ],
                        'requestId' => self::REQUEST_ID,
                    ],
                ],
                new Context(
                    self::REQUEST_ID,
                    (int)(\microtime(true) * 1000) + self::TIMEOUT_MILLISECONDS,
                    '',
                    ''
                )
            );
        } finally {
            // Terminates the kernel on both success and failure, as after each invocation
            $this->httpHandler->afterInvoke();
        }

        $statusCode = (int)($response['statusCode'] ?? 0);

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new RuntimeException(\sprintf(
                'The SnapStart warm-up request "GET %s" returned the HTTP status code %d.',
                $this->path,
                $statusCode
            ));
        }
    }

    private function isHttpHandlerFunction(): bool
    {
        $handler = LambdaContextHelper::getHandler();

        // The Bref Symfony bridge also accepts the "bootstrap-file:service" format
        return $handler === SymfonyHttpHandler::class
            || ($handler !== null && \str_ends_with($handler, ':' . SymfonyHttpHandler::class));
    }
}
