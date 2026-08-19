<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Bundle\Listener;

use EonX\EasyServerless\Bundle\Listener\TrustApiGatewayProxyRequestListener;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class TrustApiGatewayProxyRequestListenerTest extends AbstractUnitTestCase
{
    private string|false $lambdaTaskRoot = false;

    private bool $serverHadRemoteAddr = false;

    private bool $serverHadSamLocal = false;

    private mixed $serverRemoteAddr = null;

    private mixed $serverSamLocal = null;

    protected function setUp(): void
    {
        parent::setUp();

        // The listener mutates the global $_SERVER['REMOTE_ADDR'] and the process-wide trusted-proxy
        // state; capture both so tearDown can restore them instead of leaking into later tests
        $this->serverHadRemoteAddr = \array_key_exists('REMOTE_ADDR', $_SERVER);
        $this->serverRemoteAddr = $_SERVER['REMOTE_ADDR'] ?? null;
        Request::setTrustedProxies([], 0);

        // Simulate a remote Lambda so __invoke actually runs: LAMBDA_TASK_ROOT set, AWS_SAM_LOCAL not
        $this->lambdaTaskRoot = \getenv('LAMBDA_TASK_ROOT');
        \putenv('LAMBDA_TASK_ROOT=/var/task');
        $this->serverHadSamLocal = \array_key_exists('AWS_SAM_LOCAL', $_SERVER);
        $this->serverSamLocal = $_SERVER['AWS_SAM_LOCAL'] ?? null;
        unset($_SERVER['AWS_SAM_LOCAL']);
    }

    protected function tearDown(): void
    {
        Request::setTrustedProxies([], 0);

        if ($this->serverHadRemoteAddr) {
            $_SERVER['REMOTE_ADDR'] = $this->serverRemoteAddr;
        } else {
            unset($_SERVER['REMOTE_ADDR']);
        }

        if ($this->lambdaTaskRoot === false) {
            \putenv('LAMBDA_TASK_ROOT');
        } else {
            \putenv('LAMBDA_TASK_ROOT=' . $this->lambdaTaskRoot);
        }

        if ($this->serverHadSamLocal) {
            $_SERVER['AWS_SAM_LOCAL'] = $this->serverSamLocal;
        }

        parent::tearDown();
    }

    public function testDoesNothingOnSubRequest(): void
    {
        $request = $this->createRequest();

        $this->dispatch($request, HttpKernelInterface::SUB_REQUEST);

        // No trusted proxies were set, so the forwarded proto is ignored
        self::assertFalse($request->isSecure());
    }

    public function testDoesNothingOutsideRemoteLambda(): void
    {
        \putenv('LAMBDA_TASK_ROOT');
        $request = $this->createRequest();

        $this->dispatch($request);

        self::assertFalse($request->isSecure());
    }

    public function testTrustsApiGatewaySourceIpOnMainRequest(): void
    {
        $request = $this->createRequest();

        // Sanity: with no trusted proxies yet, the forwarded proto is ignored
        self::assertFalse($request->isSecure());

        $this->dispatch($request);

        // X-Forwarded-Proto is now honoured, but the spoofed X-Forwarded-For (9.9.9.9) is ignored:
        // the client IP is the platform source IP, which cannot be forged via request headers
        self::assertTrue($request->isSecure());
        self::assertSame('1.2.3.4', $request->getClientIp());
    }

    private function createRequest(): Request
    {
        return Request::create('http://api.example.com/resource', 'GET', server: [
            'REMOTE_ADDR' => '1.2.3.4',
            'HTTP_X_FORWARDED_FOR' => '9.9.9.9',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_PORT' => '443',
        ]);
    }

    private function dispatch(Request $request, int $requestType = HttpKernelInterface::MAIN_REQUEST): void
    {
        // The listener never touches the kernel, so a minimal stub is enough (avoids a mock)
        $kernel = new class implements HttpKernelInterface {
            public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
            {
                return new Response();
            }
        };

        (new TrustApiGatewayProxyRequestListener())(new RequestEvent($kernel, $request, $requestType));
    }
}
