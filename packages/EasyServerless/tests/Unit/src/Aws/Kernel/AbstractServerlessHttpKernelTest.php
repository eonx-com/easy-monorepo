<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Aws\Kernel;

use EonX\EasyServerless\Tests\Stub\Kernel\ServerlessHttpKernelStub;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

final class AbstractServerlessHttpKernelTest extends AbstractUnitTestCase
{
    protected function tearDown(): void
    {
        // Reset the static trusted-proxy state and the global mutated by the kernel
        Request::setTrustedProxies([], 0);
        unset($_SERVER['REMOTE_ADDR']);

        parent::tearDown();
    }

    public function testTrustApiGatewayProxyHonoursForwardedProto(): void
    {
        $request = $this->createRequest();

        (new ServerlessHttpKernelStub('test', false))->trustApiGatewayProxyForTest($request);

        // X-Forwarded-Proto stays trusted, so HTTPS is still detected behind API Gateway
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
        return Request::create('https://api.example.com/resource', 'GET', server: [
            'REMOTE_ADDR' => '1.2.3.4',
            'HTTP_X_FORWARDED_FOR' => '9.9.9.9',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_PORT' => '443',
        ]);
    }
}
