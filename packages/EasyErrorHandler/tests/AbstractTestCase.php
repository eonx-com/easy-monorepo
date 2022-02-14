<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests;

use EonX\EasyErrorHandler\Tests\Stubs\BaseExceptionStub;
use EonX\EasyErrorHandler\Tests\Stubs\ValidationExceptionStub;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 *
 * @covers \EonX\EasyErrorHandler\Tests\AbstractTestCase
 */
class AbstractTestCase extends TestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestRenderWithDefaultBuilders(): iterable
    {
        yield 'Returns default user message' => [
            new Request(),
            new \Exception(),
            static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                self::assertSame('exceptions.default_user_message', $content['message']);
            },
        ];

        yield 'Returns default user message translated' => [
            new Request(),
            new \Exception(),
            static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                self::assertSame('Default message', $content['message']);
            },
            null,
            [
                'exceptions.default_user_message' => 'Default message',
            ],
        ];

        yield 'Returns extended response on debug' => [
            new Request(),
            new \Exception(),
            static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                self::assertSame(['code', 'exception', 'message', 'time'], \array_keys($content));
                self::assertSame(['class', 'file', 'line', 'message', 'trace'], \array_keys($content['exception']));
            },
            [
                'easy-error-handler' => [
                    'use_extended_response' => true,
                ],
            ],
        ];

        yield 'Returns messages as is if translations are absent' => [
            new Request(),
            (new BaseExceptionStub('Exception message'))->setUserMessage('User-friendly error message'),
            static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                self::assertSame('User-friendly error message', $content['message']);
                self::assertSame('Exception message', $content['exception']['message']);
            },
            [
                'easy-error-handler' => [
                    'use_extended_response' => true,
                ],
            ],
        ];

        yield 'Returns message with params' => [
            new Request(),
            (new BaseExceptionStub('test.exception_message'))
                ->setMessageParams([
                    'param' => 'foo',
                ])
                ->setUserMessage('test.user_message')
                ->setUserMessageParams([
                    'param' => 'bar',
                ]),
            static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                self::assertSame('Exception message with foo', $content['exception']['message']);
                self::assertSame('User-friendly error message with bar', $content['message']);
            },
            [
                'easy-error-handler' => [
                    'use_extended_response' => true,
                ],
            ],
            [
                'test.exception_message' => 'Exception message with :param',
                'test.user_message' => 'User-friendly error message with :param',
            ],
        ];

        yield 'Response with 500 status code by default' => [
            new Request(),
            new \Exception(),
            static function (Response $response): void {
                self::assertSame(500, $response->getStatusCode());
            },
        ];

        yield 'Response with status code of code aware exception interface' => [
            new Request(),
            (new BaseExceptionStub())->setStatusCode(123),
            static function (Response $response): void {
                self::assertSame(123, $response->getStatusCode());
            },
        ];

        yield 'Response with sub_code' => [
            new Request(),
            (new BaseExceptionStub())->setSubCode(123456),
            static function (Response $response): void {
                $content = (array)\json_decode((string)$response->getContent(), true);
                self::assertArrayHasKey('sub_code', $content);
                self::assertSame(123456, $content['sub_code']);
            },
        ];

        yield 'Response with time in zulu format' => [
            new Request(),
            new \Exception(),
            static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                self::assertMatchesRegularExpression(
                    '/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}Z$/',
                    $content['time']
                );
            },
        ];

        yield 'Response with validation errors' => [
            new Request(),
            (new ValidationExceptionStub())->setErrors(['foo' => 'bar']),
            static function (Response $response): void {
                $content = (array)\json_decode((string)$response->getContent(), true);
                self::assertArrayHasKey('violations', $content);
                self::assertSame(['foo' => 'bar'], $content['violations']);
            },
        ];

        yield 'Short response' => [
            new Request(),
            new \Exception(),
            static function (Response $response): void {
                $content = (array)\json_decode((string)$response->getContent(), true);
                self::assertSame(['code', 'message', 'time'], \array_keys($content));
            },
        ];

        yield 'Short response with sub_code' => [
            new Request(),
            (new BaseExceptionStub())->setSubCode(123),
            static function (Response $response): void {
                $content = (array)\json_decode((string)$response->getContent(), true);
                self::assertArrayHasKey('sub_code', $content);
                self::assertSame(123, $content['sub_code']);
            },
        ];

        yield 'Short response with violations' => [
            new Request(),
            (new ValidationExceptionStub())->setErrors(['foo' => ['bar']]),
            static function (Response $response): void {
                $content = (array)\json_decode((string)$response->getContent(), true);
                self::assertArrayHasKey('violations', $content);
                self::assertSame(['foo' => ['bar']], $content['violations']);
            },
        ];

        yield 'HttpException statusCode and message' => [
            new Request(),
            new NotFoundHttpException('my-message'),
            static function (Response $response): void {
                $content = (array)\json_decode((string)$response->getContent(), true);
                self::assertSame(404, $response->getStatusCode());
                self::assertSame('my-message', $content['message']);
            },
        ];
    }

    /**
     * Returns object's private property value.
     */
    protected function getPrivatePropertyValue(object $object, string $property): mixed
    {
        return (function ($property) {
            return $this->{$property};
        })->call($object, $property);
    }

    /**
     * @param class-string|object $className
     *
     * @throws \ReflectionException
     */
    protected function getPropertyAsPublic(string|object $className, string $propertyName): ReflectionProperty
    {
        $class = new ReflectionClass($className);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);

        return $property;
    }

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $files = [__DIR__ . '/../var', __DIR__ . '/Bridge/Symfony/tmp_config.yaml'];

        foreach ($files as $file) {
            if ($fs->exists($file)) {
                $fs->remove($file);
            }
        }

        parent::tearDown();
    }
}
