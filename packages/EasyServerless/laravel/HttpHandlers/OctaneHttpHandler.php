<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Laravel\HttpHandlers;

use Bref\Context\Context;
use Bref\Event\Http\HttpRequestEvent;
use Bref\Event\Http\HttpResponse;
use Bref\LaravelBridge\Http\OctaneHandler;
use EonX\EasyServerless\Aws\Transformer\HttpEventTransformer;
use EonX\EasyServerless\Aws\Transformer\HttpEventTransformerInterface;

final class OctaneHttpHandler extends OctaneHandler
{
    private const LAMBDA_REQUEST_CONTEXT_KEY = 'LAMBDA_REQUEST_CONTEXT';

    public function __construct(
        ?string $path = null,
        private readonly HttpEventTransformerInterface $httpEventTransformer = new HttpEventTransformer(),
    ) {
        parent::__construct($path);
    }

    public function handle($event, Context $context): array
    {
        $event = $this->httpEventTransformer->transform($event);

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
