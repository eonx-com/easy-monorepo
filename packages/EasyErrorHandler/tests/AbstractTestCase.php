<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests;

use EonX\EasyErrorHandler\Tests\Stubs\BaseExceptionStub;
use EonX\EasyErrorHandler\Tests\Stubs\ValidationExceptionStub;
use Exception;
use Illuminate\Http\Request;
use LogicException;
use PHPUnit\Framework\TestCase;
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
            'request' => new Request(),
            'exception' => new Exception(),
            'assertResponse' => static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                self::assertSame('exceptions.default_user_message', $content['message']);
            },
            'config' => null,
            'translations' => null,
        ];

        yield 'Returns default user message translated' => [
            'request' => new Request(),
            'exception' => new Exception(),
            'assertResponse' => static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                self::assertSame('Default message', $content['message']);
            },
            'config' => null,
            'translations' => [
                'exceptions.default_user_message' => 'Default message',
            ],
        ];

        yield 'Returns extended response on debug' => [
            'request' => new Request(),
            'exception' => new Exception(),
            'assertResponse' => static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                self::assertSame(['code', 'exception', 'message', 'time'], \array_keys($content));
                self::assertSame(['class', 'file', 'line', 'message', 'trace'], \array_keys($content['exception']));
            },
            'config' => [
                'easy-error-handler' => [
                    'use_extended_response' => true,
                ],
            ],
            'translations' => null,
        ];

        yield 'Returns messages as is if translations are absent' => [
            'request' => new Request(),
            'exception' => (new BaseExceptionStub('Exception message'))->setUserMessage('User-friendly error message'),
            'assertResponse' => static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                self::assertSame('User-friendly error message', $content['message']);
                self::assertSame('Exception message', $content['exception']['message']);
            },
            'config' => [
                'easy-error-handler' => [
                    'use_extended_response' => true,
                ],
            ],
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
                self::assertSame('Exception message with foo', $content['exception']['message']);
                self::assertSame('User-friendly error message with bar', $content['message']);
            },
            'config' => [
                'easy-error-handler' => [
                    'use_extended_response' => true,
                ],
            ],
            'translations' => [
                'test.exception_message' => 'Exception message with :param',
                'test.user_message' => 'User-friendly error message with :param',
            ],
        ];

        yield 'Response with 500 status code by default' => [
            'request' => new Request(),
            'exception' => new Exception(),
            'assertResponse' => static function (Response $response): void {
                self::assertSame(500, $response->getStatusCode());
            },
            'config' => null,
            'translations' => null,
        ];

        yield 'Response with status code of code aware exception interface' => [
            'request' => new Request(),
            'exception' => (new BaseExceptionStub())->setStatusCode(123),
            'assertResponse' => static function (Response $response): void {
                self::assertSame(123, $response->getStatusCode());
            },
            'config' => null,
            'translations' => null,
        ];

        yield 'Response with sub_code' => [
            'request' => new Request(),
            'exception' => (new BaseExceptionStub())->setSubCode(123456),
            'assertResponse' => static function (Response $response): void {
                $content = (array)\json_decode((string)$response->getContent(), true);
                self::assertArrayHasKey('sub_code', $content);
                self::assertSame(123456, $content['sub_code']);
            },
            'config' => null,
            'translations' => null,
        ];

        yield 'Response with time in zulu format' => [
            'request' => new Request(),
            'exception' => new Exception(),
            'assertResponse' => static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                self::assertMatchesRegularExpression(
                    '/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}Z$/',
                    $content['time']
                );
            },
            'config' => null,
            'translations' => null,
        ];

        yield 'Response with validation errors' => [
            'request' => new Request(),
            'exception' => (new ValidationExceptionStub())->setErrors(['foo' => 'bar']),
            'assertResponse' => static function (Response $response): void {
                $content = (array)\json_decode((string)$response->getContent(), true);
                self::assertArrayHasKey('violations', $content);
                self::assertSame(['foo' => 'bar'], $content['violations']);
            },
            'config' => null,
            'translations' => null,
        ];

        yield 'Short response' => [
            'request' => new Request(),
            'exception' => new Exception(),
            'assertResponse' => static function (Response $response): void {
                $content = (array)\json_decode((string)$response->getContent(), true);
                self::assertSame(['code', 'message', 'time'], \array_keys($content));
            },
            'config' => null,
            'translations' => null,
        ];

        yield 'Short response with sub_code' => [
            'request' => new Request(),
            'exception' => (new BaseExceptionStub())->setSubCode(123),
            'assertResponse' => static function (Response $response): void {
                $content = (array)\json_decode((string)$response->getContent(), true);
                self::assertArrayHasKey('sub_code', $content);
                self::assertSame(123, $content['sub_code']);
            },
            'config' => null,
            'translations' => null,
        ];

        yield 'Short response with violations' => [
            'request' => new Request(),
            'exception' => (new ValidationExceptionStub())->setErrors(['foo' => ['bar']]),
            'assertResponse' => static function (Response $response): void {
                $content = (array)\json_decode((string)$response->getContent(), true);
                self::assertArrayHasKey('violations', $content);
                self::assertSame(['foo' => ['bar']], $content['violations']);
            },
            'config' => null,
            'translations' => null,
        ];

        yield 'HttpException statusCode and message' => [
            'request' => new Request(),
            'exception' => new NotFoundHttpException('my-message'),
            'assertResponse' => static function (Response $response): void {
                $content = (array)\json_decode((string)$response->getContent(), true);
                self::assertSame(404, $response->getStatusCode());
                self::assertSame('my-message', $content['message']);
            },
            'config' => null,
            'translations' => null,
        ];
    }

    protected function getPrivatePropertyValue(object $object, string $propertyName): mixed
    {
        $propertyReflection = $this->resolvePropertyReflection($object, $propertyName);
        $propertyReflection->setAccessible(true);

        return $propertyReflection->getValue($object);
    }

    protected function setPrivatePropertyValue(object $object, string $propertyName, mixed $value): void
    {
        $propertyReflection = $this->resolvePropertyReflection($object, $propertyName);
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($object, $value);
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

    private function resolvePropertyReflection(object $object, string $propertyName): ReflectionProperty
    {
        while (\property_exists($object, $propertyName) === false) {
            $object = \get_parent_class($object);

            if ($object === false) {
                throw new LogicException(\sprintf('The $%s property does not exist.', $propertyName));
            }
        }

        return new ReflectionProperty($object, $propertyName);
    }
}
