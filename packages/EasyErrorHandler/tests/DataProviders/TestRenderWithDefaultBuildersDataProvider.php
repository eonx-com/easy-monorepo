<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\DataProviders;

use EonX\EasyErrorHandler\Tests\Stubs\BaseExceptionStub;
use EonX\EasyErrorHandler\Tests\Stubs\ValidationExceptionStub;
use Exception;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class TestRenderWithDefaultBuildersDataProvider
{
    public static function provide(): iterable
    {
        yield 'Returns default user message' => [
            'request' => new Request(),
            'exception' => new Exception(),
            'assertResponse' => static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                TestCase::assertSame('Oops, something went wrong.', $content['custom_message']);
            },
            'translations' => null,
        ];

        yield 'Returns default user message translated' => [
            'request' => new Request(),
            'exception' => new Exception(),
            'assertResponse' => static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                TestCase::assertSame('Default message', $content['custom_message']);
            },
            'translations' => [
                'exceptions.default_user_message' => 'Default message',
            ],
        ];

        yield 'Response with 500 status code by default' => [
            'request' => new Request(),
            'exception' => new Exception(),
            'assertResponse' => static function (Response $response): void {
                TestCase::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
            },
            'translations' => null,
        ];

        yield 'Response with status code of code aware exception interface' => [
            'request' => new Request(),
            'exception' => (new BaseExceptionStub())->setStatusCode(123),
            'assertResponse' => static function (Response $response): void {
                TestCase::assertSame(123, $response->getStatusCode());
            },
            'translations' => null,
        ];

        yield 'Response with sub_code' => [
            'request' => new Request(),
            'exception' => (new BaseExceptionStub())->setSubCode(123456),
            'assertResponse' => static function (Response $response): void {
                $content = (array)\json_decode((string)$response->getContent(), true);
                TestCase::assertArrayHasKey('custom_sub_code', $content);
                TestCase::assertSame(123456, $content['custom_sub_code']);
            },
            'translations' => null,
        ];

        yield 'Response with time in zulu format' => [
            'request' => new Request(),
            'exception' => new Exception(),
            'assertResponse' => static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                TestCase::assertMatchesRegularExpression(
                    '/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}Z$/',
                    $content['custom_time']
                );
            },
            'translations' => null,
        ];

        yield 'Response with validation errors' => [
            'request' => new Request(),
            'exception' => (new ValidationExceptionStub())->setErrors(['foo' => 'bar']),
            'assertResponse' => static function (Response $response): void {
                $content = (array)\json_decode((string)$response->getContent(), true);
                TestCase::assertArrayHasKey('custom_violations', $content);
                TestCase::assertSame(['foo' => 'bar'], $content['custom_violations']);
            },
            'translations' => null,
        ];

        yield 'Short response' => [
            'request' => new Request(),
            'exception' => new Exception(),
            'assertResponse' => static function (Response $response): void {
                $content = (array)\json_decode((string)$response->getContent(), true);
                TestCase::assertSame(['custom_code', 'custom_message', 'custom_time'], \array_keys($content));
            },
            'translations' => null,
        ];

        yield 'Short response with sub_code' => [
            'request' => new Request(),
            'exception' => (new BaseExceptionStub())->setSubCode(123),
            'assertResponse' => static function (Response $response): void {
                $content = (array)\json_decode((string)$response->getContent(), true);
                TestCase::assertArrayHasKey('custom_sub_code', $content);
                TestCase::assertSame(123, $content['custom_sub_code']);
            },
            'translations' => null,
        ];

        yield 'Short response with violations' => [
            'request' => new Request(),
            'exception' => (new ValidationExceptionStub())->setErrors(['foo' => ['bar']]),
            'assertResponse' => static function (Response $response): void {
                $content = (array)\json_decode((string)$response->getContent(), true);
                TestCase::assertArrayHasKey('custom_violations', $content);
                TestCase::assertSame(['foo' => ['bar']], $content['custom_violations']);
            },
            'translations' => null,
        ];

        yield 'HttpException statusCode and message' => [
            'request' => new Request(),
            'exception' => new NotFoundHttpException('my-message'),
            'assertResponse' => static function (Response $response): void {
                $content = (array)\json_decode((string)$response->getContent(), true);
                TestCase::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
                TestCase::assertSame('my-message', $content['custom_message']);
            },
            'translations' => null,
        ];
    }

    public static function provideWithExtendedResponse(): iterable
    {
        yield 'Returns extended response on debug' => [
            'request' => new Request(),
            'exception' => new Exception(),
            'assertResponse' => static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                TestCase::assertSame(
                    ['custom_code', 'custom_exception', 'custom_message', 'custom_time'],
                    \array_keys($content)
                );
                TestCase::assertSame(
                    ['custom_class', 'custom_file', 'custom_line', 'custom_message', 'custom_trace'],
                    \array_keys($content['custom_exception'])
                );
            },
            'translations' => null,
        ];

        yield 'Returns messages as is if translations are absent' => [
            'request' => new Request(),
            'exception' => (new BaseExceptionStub('Exception message'))
                ->setUserMessage('User-friendly error message'),
            'assertResponse' => static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                TestCase::assertSame('User-friendly error message', $content['custom_message']);
                TestCase::assertSame('Exception message', $content['custom_exception']['custom_message']);
            },
            'translations' => null,
        ];

        yield 'Returns message with params' => [
            'request' => new Request(),
            'exception' => (new BaseExceptionStub('test.exception_message'))
                ->setMessageParams([
                    'param' => 'foo',
                ])
                ->setUserMessage('test.user_message')
                ->setUserMessageParams([
                    'param' => 'bar',
                ]),
            'assertResponse' => static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                TestCase::assertSame('Exception message with foo', $content['custom_exception']['custom_message']);
                TestCase::assertSame('User-friendly error message with bar', $content['custom_message']);
            },
            'translations' => [
                'test.exception_message' => 'Exception message with $param',
                'test.user_message' => 'User-friendly error message with $param',
            ],
        ];
    }
}
