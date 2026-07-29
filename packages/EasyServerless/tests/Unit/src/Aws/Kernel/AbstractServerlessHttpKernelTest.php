<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Aws\Kernel;

use EonX\EasyServerless\Tests\Stub\Kernel\ServerlessHttpKernelStub;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

final class AbstractServerlessHttpKernelTest extends AbstractUnitTestCase
{
    private bool $serverHadRemoteAddr = false;

    private ?string $serverRemoteAddr = null;

    protected function setUp(): void
    {
        parent::setUp();

        // trustApiGatewayProxy() mutates the global $_SERVER['REMOTE_ADDR']; capture its prior
        // state so tearDown can restore it instead of leaking a value into later tests
        $this->serverHadRemoteAddr = \array_key_exists('REMOTE_ADDR', $_SERVER);
        $this->serverRemoteAddr = $_SERVER['REMOTE_ADDR'] ?? null;

        // Start from a clean trusted-proxy state so the "before" assertion is deterministic
        Request::setTrustedProxies([], 0);
    }

    protected function tearDown(): void
    {
        Request::setTrustedProxies([], 0);

        if ($this->serverHadRemoteAddr) {
            $_SERVER['REMOTE_ADDR'] = $this->serverRemoteAddr;
        } else {
            unset($_SERVER['REMOTE_ADDR']);
        }

        parent::tearDown();
    }

    public function testTrustApiGatewayProxyHonoursForwardedProto(): void
    {
        // The base request is plain http, so isSecure() is driven purely by whether
        // X-Forwarded-Proto is trusted
        $request = $this->createRequest();

        // Sanity: with no trusted proxies yet, the forwarded proto is ignored
        self::assertFalse($request->isSecure());

        (new ServerlessHttpKernelStub('test', false))->trustApiGatewayProxyForTest($request);

        // Only after trusting the API Gateway proxy is X-Forwarded-Proto honoured
        self::assertTrue($request->isSecure());
    }

    public function testTrustApiGatewayProxyIgnoresSpoofedForwardedFor(): void
    {
        $request = $this->createRequest();

        (new ServerlessHttpKernelStub('test', false))->trustApiGatewayProxyForTest($request);

        // The caller-supplied X-Forwarded-For (9.9.9.9) is ignored: the client IP is the
        // platform-provided source IP, which cannot be spoofed via request headers
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
}
