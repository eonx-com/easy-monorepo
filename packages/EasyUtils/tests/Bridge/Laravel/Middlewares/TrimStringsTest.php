<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Bridge\Laravel\Middlewares;

use EonX\EasyUtils\Bridge\Laravel\Middlewares\TrimStrings;
use EonX\EasyUtils\StringTrimmers\StringTrimmerInterface;
use EonX\EasyUtils\Tests\AbstractTestCase;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

final class TrimStringsTest extends AbstractTestCase
{
    public function testHandleSucceedsWithGetRequest(): void
    {
        $data = [
            'abc' => '  123  ',
        ];
        $except = [];
        $expectedResult = [
            'abc' => '123',
        ];
        /** @var \EonX\EasyUtils\StringTrimmers\StringTrimmerInterface $trimmer */
        $trimmer = $this->mock(
            StringTrimmerInterface::class,
            static function (MockInterface $mock) use ($data, $except, $expectedResult): void {
                $mock->shouldReceive('trim')
                    ->once()
                    ->with($data, $except)
                    ->andReturn($expectedResult);
                $mock->shouldReceive('trim')
                    ->once()
                    ->with([], [])
                    ->andReturn([]);
            }
        );
        $middleware = new TrimStrings($trimmer, $except);
        $symfonyRequest = new SymfonyRequest($data);
        $symfonyRequest->server->set('REQUEST_METHOD', 'GET');
        $request = Request::createFromBase($symfonyRequest);

        $result = $middleware->handle($request, static function (Request $request): string {
            return $request->get('abc');
        });

        self::assertSame('123', $result);
    }

    public function testHandleSucceedsWithJsonRequest(): void
    {
        $data = [
            'abc' => '  123  ',
        ];
        $json = (string)\json_encode($data);
        $except = [];
        $expectedResult = [
            'abc' => '123',
        ];
        /** @var \EonX\EasyUtils\StringTrimmers\StringTrimmerInterface $trimmer */
        $trimmer = $this->mock(
            StringTrimmerInterface::class,
            static function (MockInterface $mock) use ($data, $except, $expectedResult): void {
                $mock->shouldReceive('trim')
                    ->once()
                    ->with([], $except)->andReturn([]);
                $mock->shouldReceive('trim')
                    ->once()
                    ->with($data, $except)
                    ->andReturn($expectedResult);
            }
        );
        $middleware = new TrimStrings($trimmer, $except);
        $symfonyRequest = new SymfonyRequest([], [], [], [], [], [], $json);
        $symfonyRequest->server->set('REQUEST_METHOD', 'POST');
        $symfonyRequest->headers = new HeaderBag([
            'CONTENT_TYPE' => 'application/json',
        ]);
        $request = Request::createFromBase($symfonyRequest);

        $result = $middleware->handle($request, static function (Request $request): string {
            return $request->json('abc');
        });

        self::assertSame('123', $result);
    }

    public function testHandleSucceedsWithPostRequest(): void
    {
        $data = [
            'abc' => '  123  ',
        ];
        $except = [];
        $expectedResult = [
            'abc' => '123',
        ];
        /** @var \EonX\EasyUtils\StringTrimmers\StringTrimmerInterface $trimmer */
        $trimmer = $this->mock(
            StringTrimmerInterface::class,
            static function (MockInterface $mock) use ($data, $except, $expectedResult): void {
                $mock->shouldReceive('trim')
                    ->once()
                    ->with([], $except)->andReturn([]);
                $mock->shouldReceive('trim')
                    ->once()
                    ->with($data, $except)
                    ->andReturn($expectedResult);
            }
        );
        $middleware = new TrimStrings($trimmer, $except);
        $symfonyRequest = new SymfonyRequest([], $data);
        $symfonyRequest->server->set('REQUEST_METHOD', 'POST');
        $request = Request::createFromBase($symfonyRequest);

        $result = $middleware->handle($request, static function (Request $request): string {
            return $request->get('abc');
        });

        self::assertSame('123', $result);
    }
}
