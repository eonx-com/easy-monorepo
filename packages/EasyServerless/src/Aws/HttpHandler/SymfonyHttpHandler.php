<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\HttpHandler;

use Bref\Context\Context;
use Bref\Event\Http\HttpHandler;
use Bref\Event\Http\HttpRequestEvent;
use Bref\Event\Http\HttpResponse;
use Bref\Event\Http\Psr7Bridge;
use EonX\EasyServerless\Aws\Transformer\HttpEventTransformer;
use EonX\EasyServerless\Aws\Transformer\HttpEventTransformerInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

/**
 * This HTTP handler has different benefits compared to the standard PSR-15 handler from Bref:
 * - Defer calling the TerminableInterface::terminate() method until after the Lambda function has returned,
 * - Set missing environment variable with the Lambda request context,
 * - Allow to transform the raw Lambda HTTP event before processing it,
 */
final class SymfonyHttpHandler extends HttpHandler
{
    private const LAMBDA_REQUEST_CONTEXT_KEY = 'LAMBDA_REQUEST_CONTEXT';

    private readonly HttpMessageFactoryInterface $psrHttpFactory;

    private ?Request $symfonyRequest = null;

    private ?Response $symfonyResponse = null;

    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly HttpFoundationFactoryInterface $httpFoundationFactory = new HttpFoundationFactory(),
        private readonly HttpEventTransformerInterface $httpEventTransformer = new HttpEventTransformer(),
        ?HttpMessageFactoryInterface $psrHttpFactory = null,
    ) {
        if ($psrHttpFactory === null) {
            $psr17Factory = new Psr17Factory();
            $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        }

        $this->psrHttpFactory = $psrHttpFactory;
    }

    public function afterInvoke(): void
    {
        if ($this->symfonyRequest === null || $this->symfonyResponse === null) {
            return;
        }

        if ($this->kernel instanceof TerminableInterface) {
            $this->kernel->terminate($this->symfonyRequest, $this->symfonyResponse);
        }
    }

    public function handle(mixed $event, Context $context): array
    {
        if (\is_array($event)) {
            $event = $this->httpEventTransformer->transform($event);
        }

        return parent::handle($event, $context);
    }

    /**
     * @throws \Exception
     */
    public function handleRequest(HttpRequestEvent $event, Context $context): HttpResponse
    {
        $this->reset();
        $this->setRequestContext((string)\json_encode($event->getRequestContext()));

        $symfonyRequest = $this->httpFoundationFactory->createRequest(Psr7Bridge::convertRequest($event, $context));
        $symfonyResponse = $this->kernel->handle($symfonyRequest);

        $this->symfonyRequest = $symfonyRequest;
        $this->symfonyResponse = $symfonyResponse;

        return Psr7Bridge::convertResponse($this->psrHttpFactory->createResponse($symfonyResponse));
    }

    private function reset(): void
    {
        $this->symfonyRequest = null;
        $this->symfonyResponse = null;

        Psr7Bridge::cleanupUploadedFiles();
    }

    private function setRequestContext(string $value): void
    {
        $_SERVER[self::LAMBDA_REQUEST_CONTEXT_KEY] = $_ENV[self::LAMBDA_REQUEST_CONTEXT_KEY] = $value;
        \putenv(\sprintf('%s=%s', self::LAMBDA_REQUEST_CONTEXT_KEY, $value));
    }
}
