<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyCore\Tests\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LoyaltyCorp\EasyCore\Http\Middleware\SetContentLength;
use LoyaltyCorp\EasyCore\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @covers \LoyaltyCorp\EasyCore\Http\Middleware\SetContentLength
 *
 * @internal
 */
final class SetContentLengthTest extends AbstractTestCase
{
    /**
     * Test handle successfully.
     */
    public function testHandleSucceeds(): void
    {
        $middleware = new SetContentLength();
        $requestProphecy = $this->prophesize(Request::class);
        /** @var \Illuminate\Http\Request $request */
        $request = $requestProphecy->reveal();
        $responseProphecy = $this->prophesize(Response::class);
        $responseProphecy->getContent()->willReturn('content');
        $response = $responseProphecy->reveal();
        $headersProphecy = $this->prophesize(ResponseHeaderBag::class);
        // 'content' string's length is 7
        $headersProphecy->set('Content-Length', 7)->willReturn();
        $response->headers = $headersProphecy->reveal();
        $next = static function ($request) use ($response) {
            $response->forRequest = $request;

            return $response;
        };

        $actualResult = $middleware->handle($request, $next);

        self::assertSame($actualResult, $response);
        self::assertSame($response->forRequest, $request);
        $responseProphecy->getContent()->shouldHaveBeenCalledOnce();
        $headersProphecy->set('Content-Length', 7)->shouldHaveBeenCalledOnce();
    }
}
