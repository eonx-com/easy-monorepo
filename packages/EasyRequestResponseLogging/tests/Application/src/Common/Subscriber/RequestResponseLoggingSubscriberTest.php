<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Tests\Application\Common\Subscriber;

use EonX\EasyRequestResponseLogging\Bundle\Enum\BundleParam;
use EonX\EasyRequestResponseLogging\Tests\Application\AbstractApplicationTestCase;
use EonX\EasyRequestResponseLogging\Tests\Fixture\App\Listener\EarlyResponseListener;
use EonX\EasyRequestResponseLogging\Tests\Fixture\App\Listener\SlowTerminateListener;
use EonX\EasyTest\Monolog\Processor\LogCollectorProcessor;
use EonX\EasyTest\Monolog\Trait\MonologTrait;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;

final class RequestResponseLoggingSubscriberTest extends AbstractApplicationTestCase
{
    use MonologTrait;

    /**
     * @see testOnKernelTerminateSucceedsAndDoesNotLogNonLoggableContentTypeBody
     */
    public static function provideNonLoggableContentTypeBodies(): iterable
    {
        yield 'multipart' => ['multipart/form-data; boundary=some-boundary', 'some-binary-content'];

        yield 'plain text' => ['text/plain', 'password: some-password'];

        yield 'xml' => ['application/xml', '<password>some-password</password>'];

        yield 'xml suffix' => ['application/soap+xml', '<password>some-password</password>'];

        yield 'text xml' => ['text/xml', '<password>some-password</password>'];
    }

    /**
     * @see testOnKernelTerminateSucceedsAndDoesNotLogNonSanitizableBody
     */
    public static function provideNonSanitizableBodies(): iterable
    {
        yield 'malformed json' => ['{"password":"some-password"'];

        yield 'number out of range' => ['{"password":"some-password","amount":1e400}'];
    }

    /**
     * @see testOnKernelTerminateSucceedsWithSanitizedPath
     */
    public static function provideSensitivePaths(): iterable
    {
        yield 'plain segment name' => ['/token/some-token', '/token/*REDACTED*'];

        yield 'percent-encoded segment name' => ['/%74oken/some-token', '/token/*REDACTED*'];

        yield 'percent-encoded delimiter in value' => ['/token/some%2Ftoken%26more', '/token/*REDACTED*'];

        yield 'percent-encoded non-sensitive segment' => ['/some%20path/some-value', '/some%20path/some-value'];

        yield 'literal ampersand in value' => ['/token/first&second', '/token/*REDACTED*'];

        yield 'literal key=value segment' => ['/token=some-token', '/token=*REDACTED*'];

        yield 'percent-encoded slash after segment name' => ['/token%2Fsome-token', '/token/*REDACTED*'];

        yield 'percent-encoded equals after segment name' => ['/token%3Dsome-token', '/token=*REDACTED*'];

        yield 'percent-encoded nested segments' => ['/some%2Ftoken%2Fsome-token', '/some/token/*REDACTED*'];

        yield 'query-like segment' => ['/some/name=John&token=first,second', '/some/name=John&token=*REDACTED*'];

        yield 'uppercase segment name' => ['/TOKEN/some-token', '/TOKEN/*REDACTED*'];

        yield 'literal delimiters in key=value segment' => [
            "/some/token=first,second;third'fourth",
            '/some/token=*REDACTED*',
        ];
    }

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
        self::assertFalse($requestContext['body_omitted']);
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
        self::assertFalse($responseContext['body_omitted']);
        self::assertFalse($responseContext['body_truncated']);
        self::assertGreaterThan(0.0, $responseContext['duration_ms']);
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

    #[DataProvider('provideNonLoggableContentTypeBodies')]
    public function testOnKernelTerminateSucceedsAndDoesNotLogNonLoggableContentTypeBody(
        string $contentType,
        string $content,
    ): void {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create(
            uri: '/response',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => $contentType,
            ],
            content: $content
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $requestContext = self::getSingleRecord()->context['request'];
        self::assertIsArray($requestContext);
        self::assertNull($requestContext['body']);
        self::assertFalse($requestContext['body_omitted']);
        self::assertFalse($requestContext['body_truncated']);
    }

    #[DataProvider('provideNonSanitizableBodies')]
    public function testOnKernelTerminateSucceedsAndDoesNotLogNonSanitizableBody(string $content): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create(
            uri: '/response',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
            ],
            content: $content
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $requestContext = self::getSingleRecord()->context['request'];
        self::assertIsArray($requestContext);
        self::assertNull($requestContext['body']);
        self::assertTrue($requestContext['body_omitted']);
        self::assertFalse($requestContext['body_truncated']);
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
        self::assertTrue($requestContext['body_omitted']);
        self::assertFalse($requestContext['body_truncated']);
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

    public function testOnKernelTerminateSucceedsWithBodiesDisabled(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel([
            'environment' => 'test_bodies_disabled',
        ]);
        $request = Request::create(
            uri: '/response',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
            ],
            content: '{"name":"John"}'
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $record = self::getSingleRecord();
        $requestContext = $record->context['request'];
        $responseContext = $record->context['response'];
        self::assertIsArray($requestContext);
        self::assertIsArray($responseContext);
        self::assertNull($requestContext['body']);
        self::assertFalse($requestContext['body_omitted']);
        self::assertFalse($requestContext['body_truncated']);
        self::assertNull($responseContext['body']);
        self::assertFalse($responseContext['body_omitted']);
        self::assertFalse($responseContext['body_truncated']);
    }

    public function testOnKernelTerminateSucceedsWithFormBody(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create(
            uri: '/response',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            ],
            content: 'user%5Bname%5D=John&user%5Bpassword%5D=some-password&password=some-password'
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $requestContext = self::getSingleRecord()->context['request'];
        self::assertIsArray($requestContext);
        self::assertSame(
            'user[name]=John&user[password]=*REDACTED*&password=*REDACTED*',
            $requestContext['body']
        );
        self::assertFalse($requestContext['body_truncated']);
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

    public function testOnKernelTerminateSucceedsWithNullDurationWhenResponseIsReturnedEarly(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create(
            uri: '/response',
            server: [
                'HTTP_' . \strtoupper(\str_replace('-', '_', EarlyResponseListener::HEADER)) => '1',
            ]
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $responseContext = self::getSingleRecord()->context['response'];
        self::assertIsArray($responseContext);
        self::assertNull($responseContext['duration_ms']);
    }

    #[DataProvider('provideSensitivePaths')]
    public function testOnKernelTerminateSucceedsWithSanitizedPath(string $path, string $expectedPath): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create($path);

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $record = self::getSingleRecord();
        $requestContext = $record->context['request'];
        self::assertIsArray($requestContext);
        self::assertSame(\sprintf('Request: "GET %s" -> 404', $expectedPath), $record->message);
        self::assertSame($expectedPath, $requestContext['uri']);
    }

    public function testOnKernelTerminateSucceedsWithSanitizedRequestIds(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create(
            uri: '/response',
            server: [
                'HTTP_X_CORRELATION_ID' => '5555555555554444',
                'HTTP_X_REQUEST_ID' => '4111111111111111',
            ]
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $record = self::getSingleRecord();
        self::assertSame('555555*REDACTED*4444', $record->context['correlation_id']);
        self::assertSame('411111*REDACTED*1111', $record->context['request_id']);
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
        self::assertFalse($requestContext['body_omitted']);
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
