<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Laravel\HttpHandlers;

use Bref\Context\Context;
use Bref\Event\Http\HttpRequestEvent;
use Bref\Event\Http\HttpResponse;
use Bref\LaravelBridge\Http\OctaneHandler;
use EonX\EasyServerless\Aws\Transformer\HttpEventTransformer;
use EonX\EasyServerless\Aws\Transformer\HttpEventTransformerInterface;
use Psr\Log\LoggerInterface;

final class OctaneHttpHandler extends OctaneHandler
{
    private const LAMBDA_REQUEST_CONTEXT_KEY = 'LAMBDA_REQUEST_CONTEXT';

    private const SAFETY_TIMEOUT_MARGIN_MILLISECONDS = 1000; // 1 second

    public function __construct(
        ?string $path = null,
        private readonly HttpEventTransformerInterface $httpEventTransformer = new HttpEventTransformer(),
        private readonly ?LoggerInterface $logger = null,
        private readonly int $lambdaTimeoutSeconds = 0,
    ) {
        parent::__construct($path);
    }

    public function afterInvoke(Context $context): void
    {
        $remainingTimeInMilliseconds = $context->getRemainingTimeInMillis() - self::SAFETY_TIMEOUT_MARGIN_MILLISECONDS;
        $timeoutThresholdMilliseconds = $this->lambdaTimeoutSeconds * 1000;

        if ($remainingTimeInMilliseconds <= $timeoutThresholdMilliseconds) {
            $this->logger?->debug(
                \sprintf(
                    'Do nothing and wait because remaining Lambda time (%d ms) is below threshold (%d ms)',
                    $remainingTimeInMilliseconds,
                    $timeoutThresholdMilliseconds
                )
            );

            \sleep($this->lambdaTimeoutSeconds);
        }
    }

    public function handle(mixed $event, Context $context): array
    {
        if (\is_array($event)) {
            $event = $this->httpEventTransformer->transform($event);
        }

        return parent::handle($event, $context);
    }

    public function handleRequest(HttpRequestEvent $event, Context $context): HttpResponse
    {
        $this->setRequestContext((string)\json_encode($event->getRequestContext()));

        return parent::handleRequest($event, $context);
    }

    private function setRequestContext(string $value): void
    {
        $_SERVER[self::LAMBDA_REQUEST_CONTEXT_KEY] = $_ENV[self::LAMBDA_REQUEST_CONTEXT_KEY] = $value;
        \putenv(\sprintf('%s=%s', self::LAMBDA_REQUEST_CONTEXT_KEY, $value));
    }
}
