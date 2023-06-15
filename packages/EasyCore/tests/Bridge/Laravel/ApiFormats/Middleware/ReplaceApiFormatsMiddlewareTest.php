<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Laravel\ApiFormats\Middleware;

use EonX\EasyCore\Bridge\Laravel\ApiFormats\Middleware\ReplaceApiFormatsMiddleware;
use EonX\EasyCore\Bridge\Laravel\ApiFormats\Responses\FormattedApiResponse;
use EonX\EasyCore\Bridge\Laravel\ApiFormats\Responses\NoContentApiResponse;
use EonX\EasyCore\Tests\AbstractTestCase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ReplaceApiFormatsMiddlewareTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testHandle
     */
    public function providerTestHandle(): iterable
    {
        yield 'Array' => [
            [
                'key' => 'value',
            ],
        ];

        yield 'Formatted api response' => [
            new FormattedApiResponse(\json_encode([
                'key' => 'value',
            ])),
        ];

        yield 'No content response' => [new NoContentApiResponse(), NoContentApiResponse::class];

        yield 'Symfony response' => [new Response(''), Response::class];
    }

    /**
     * @param mixed $apiResponse
     * @param null|class-string $expectedResponseClass
     *
     * @dataProvider providerTestHandle
     */
    public function testHandle($apiResponse, ?string $expectedResponseClass = null): void
    {
        $middleware = new ReplaceApiFormatsMiddleware();

        $response = $middleware->handle(new Request(), static function (Request $request) use ($apiResponse) {
            return $apiResponse;
        });

        self::assertInstanceOf($expectedResponseClass ?? JsonResponse::class, $response);
    }
}
