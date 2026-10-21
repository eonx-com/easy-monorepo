<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Tests\Application\Common\Listener;

use EonX\EasyRequestResponseLogging\Bundle\Enum\BundleParam;
use EonX\EasyRequestResponseLogging\Tests\Application\AbstractApplicationTestCase;
use EonX\EasyRequestResponseLogging\Tests\Fixture\App\Listener\SlowTerminateListener;
use EonX\EasyTest\Monolog\Processor\LogCollectorProcessor;
use EonX\EasyTest\Monolog\Trait\MonologTrait;
use Monolog\Level;
use Monolog\LogRecord;
use Symfony\Component\HttpFoundation\Request;

final class RequestResponseLoggingListenerTest extends AbstractApplicationTestCase
{
    use MonologTrait;

    public function testOnKernelTerminateSucceeds(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create(
            uri: '/response?apikey=some-api-key&name=John',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer some-token',
                'HTTP_COOKIE' => 'session=some-session',
                'HTTP_X_API_KEY' => 'some-api-key',
                'HTTP_X_CORRELATION_ID' => 'some-correlation-id',
                'HTTP_X_REQUEST_ID' => 'some-request-id',
            ],
            content: '{"name":"John","note":"4111111111111111"}'
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $record = self::getSingleRecord();
        $requestContext = $record->context['request'];
        $responseContext = $record->context['response'];
        self::assertIsArray($requestContext);
        self::assertIsArray($requestContext['headers']);
        self::assertIsArray($responseContext);
        self::assertSame(Level::Info, $record->level);
        self::assertSame('Request: "POST /response" -> 200', $record->message);
        self::assertSame('some-correlation-id', $record->context['correlation_id']);
        self::assertSame('some-request-id', $record->context['request_id']);
        self::assertSame('{"name":"John","note":"411111*REDACTED*1111"}', $requestContext['body']);
        self::assertFalse($requestContext['body_truncated']);
        self::assertSame('*REDACTED*', $requestContext['headers']['authorization']);
        self::assertSame('*REDACTED*', $requestContext['headers']['cookie']);
        self::assertSame('*REDACTED*', $requestContext['headers']['x-api-key']);
        self::assertSame('POST', $requestContext['method']);
        self::assertSame(
            [
                'apikey' => '*REDACTED*',
                'name' => 'John',
            ],
            $requestContext['query']
        );
        self::assertSame('/response', $requestContext['uri']);
        self::assertSame('{"card_token":"*REDACTED*","name":"John"}', $responseContext['body']);
        self::assertFalse($responseContext['body_truncated']);
        self::assertGreaterThanOrEqual(0.0, $responseContext['duration_ms']);
        self::assertSame(200, $responseContext['status_code']);
    }

    public function testOnKernelTerminateSucceedsAndDoesNotLogDoNotLogPath(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create('/ping');

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        self::assertSame([], self::getRecords());
    }

    public function testOnKernelTerminateSucceedsAndDoesNotLogNonLoggableContentTypeBody(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create(
            uri: '/response',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'multipart/form-data; boundary=some-boundary',
            ],
            content: 'some-binary-content'
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $requestContext = self::getSingleRecord()->context['request'];
        self::assertIsArray($requestContext);
        self::assertNull($requestContext['body']);
        self::assertFalse($requestContext['body_truncated']);
    }

    public function testOnKernelTerminateSucceedsAndDoesNotLogNonSanitizableBody(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create(
            uri: '/response',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
            ],
            content: '{"password":"some-password","amount":1e400}'
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $requestContext = self::getSingleRecord()->context['request'];
        self::assertIsArray($requestContext);
        self::assertNull($requestContext['body']);
        self::assertTrue($requestContext['body_truncated']);
    }

    public function testOnKernelTerminateSucceedsAndDoesNotLogTooBigBody(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create(
            uri: '/response',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
            ],
            content: \json_encode([
                'password' => 'some-password',
                'description' => \str_repeat('a', 5000),
            ], \JSON_THROW_ON_ERROR)
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $requestContext = self::getSingleRecord()->context['request'];
        self::assertIsArray($requestContext);
        self::assertNull($requestContext['body']);
        self::assertTrue($requestContext['body_truncated']);
    }

    public function testOnKernelTerminateSucceedsAndExcludesOtherTerminateListenersFromDuration(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create(
            uri: '/response',
            server: [
                'HTTP_' . \strtoupper(\str_replace('-', '_', SlowTerminateListener::HEADER)) => '1',
            ]
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $responseContext = self::getSingleRecord()->context['response'];
        self::assertIsArray($responseContext);
        self::assertLessThan(300.0, $responseContext['duration_ms']);
    }

    public function testOnKernelTerminateSucceedsWithJsonSuffixContentTypeBody(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create(
            uri: '/response',
            method: 'PATCH',
            server: [
                'CONTENT_TYPE' => 'application/merge-patch+json; charset=utf-8',
            ],
            content: '{"password":"some-password"}'
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $requestContext = self::getSingleRecord()->context['request'];
        self::assertIsArray($requestContext);
        self::assertSame('{"password":"*REDACTED*"}', $requestContext['body']);
    }

    public function testOnKernelTerminateSucceedsWithSanitizedTruncatedBody(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create(
            uri: '/response',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
            ],
            content: \json_encode([
                'password' => 'some-password',
                'description' => \str_repeat('a', 2048),
            ], \JSON_THROW_ON_ERROR)
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $requestContext = self::getSingleRecord()->context['request'];
        self::assertIsArray($requestContext);
        self::assertIsString($requestContext['body']);
        self::assertTrue($requestContext['body_truncated']);
        self::assertSame(1024, \strlen($requestContext['body']));
        self::assertStringStartsWith('{"password":"*REDACTED*","description":"aaa', $requestContext['body']);
    }

    /**
     * @return \Monolog\LogRecord[]
     */
    private static function getRecords(): array
    {
        return \array_values(\array_filter(
            LogCollectorProcessor::getRecords(),
            static fn (LogRecord $record): bool => $record->channel === BundleParam::LogChannel->value
        ));
    }

    private static function getSingleRecord(): LogRecord
    {
        $records = self::getRecords();

        self::assertCount(1, $records);

        return $records[0];
    }
}
