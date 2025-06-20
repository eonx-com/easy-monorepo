<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\HttpHandler;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

/**
 * The main purpose of this class is to defer calling the KernelInterface::terminate()
 * method until after the Lambda function has returned. This will allow us to check the state of the kernel
 * between invocations and force Bref to exit if needed.
 * An indirect benefit is that it allows applications to delay specific logic until after the response has been sent.
 */
final class SymfonyHttpHandler implements RequestHandlerInterface
{
    private ?Request $symfonyRequest = null;

    private ?Response $symfonyResponse = null;

    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly HttpFoundationFactoryInterface $httpFoundationFactory = new HttpFoundationFactory(),
        private ?HttpMessageFactoryInterface $psrHttpFactory = null
    ) {
        if ($this->psrHttpFactory === null) {
            $psr17Factory = new Psr17Factory();
            $this->psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        }
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

    /**
     * @throws \Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Reset just to be safe
        $this->symfonyRequest = null;
        $this->symfonyResponse = null;

        $symfonyRequest = $this->httpFoundationFactory->createRequest($request);
        $symfonyResponse = $this->kernel->handle($symfonyRequest);

        $this->symfonyRequest = $symfonyRequest;
        $this->symfonyResponse = $symfonyResponse;

        return $this->psrHttpFactory->createResponse($symfonyResponse);
    }
}
