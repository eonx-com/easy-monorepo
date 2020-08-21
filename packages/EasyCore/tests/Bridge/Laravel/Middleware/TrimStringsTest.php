<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Laravel\Middleware;

use EonX\EasyCore\Bridge\Laravel\Middleware\TrimStrings;
use EonX\EasyCore\Tests\AbstractTestCase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * @covers \EonX\EasyCore\Bridge\Laravel\Middleware\TrimStrings
 *
 * @internal
 */
final class TrimStringsTest extends AbstractTestCase
{
    public function testHandleSucceeds(): void
    {
        $middleware = new TrimStrings();
        $symfonyRequest = new SymfonyRequest([
            'abc' => '  123  ',
            'xyz' => '  456  ',
            'foo' => '  abc  ',
            'bar' => '  ZXY  ',
            'recursion' => [
                '  123  ',
                '  456  ',
            ],
            'recursion_with_2_level' => [
                'recursion' => [
                    '  abc  ',
                    '  ZXY  ',
                ],
                '  123  ',
                '  456  ',
            ],
        ]);
        $symfonyRequest->server->set('REQUEST_METHOD', 'GET');
        $request = Request::createFromBase($symfonyRequest);

        $middleware->handle($request, static function (Request $request): void {
            self::assertSame('123', $request->get('abc'));
            self::assertSame('456', $request->get('xyz'));
            self::assertSame('abc', $request->get('foo'));
            self::assertSame('ZXY', $request->get('bar'));
            self::assertSame([
                '123',
                '456',
            ], $request->get('recursion'));
            self::assertSame([
                'recursion' => [
                    'abc',
                    'ZXY',
                ],
                '123',
                '456',
            ], $request->get('recursion_with_2_level'));
        });
    }
}
