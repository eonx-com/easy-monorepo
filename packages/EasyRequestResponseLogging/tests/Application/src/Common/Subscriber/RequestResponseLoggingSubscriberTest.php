<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Tests\Application\Common\Subscriber;

use EonX\EasyRequestResponseLogging\Bundle\Enum\BundleParam;
use EonX\EasyRequestResponseLogging\Tests\Application\AbstractApplicationTestCase;
use EonX\EasyRequestResponseLogging\Tests\Fixture\App\Controller\ResponseController;
use EonX\EasyRequestResponseLogging\Tests\Fixture\App\Listener\EarlyResponseListener;
use EonX\EasyRequestResponseLogging\Tests\Fixture\App\Listener\LastTerminateListener;
use EonX\EasyRequestResponseLogging\Tests\Fixture\App\Listener\SlowTerminateListener;
use EonX\EasyRequestResponseLogging\Tests\Fixture\App\Processor\FailingProcessor;
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
        yield 'route parameter masked by name' => ['/token/some-token', '/token/*REDACTED*'];

        yield 'route parameter masked by value' => ['/lookup/4111111111111111', '/lookup/411111*REDACTED*1111'];

        yield 'percent-encoded route parameter' => [
            '/lookup/4111%201111%201111%201111',
            '/lookup/411111*REDACTED*1111',
        ];

        yield 'percent-encoded static segment' => ['/%74oken/some-token', '/token/*REDACTED*'];

        yield 'route parameter with encoded and literal delimiters' => [
            '/token/first%2Fsecret%26more/second,third',
            '/token/*REDACTED*',
        ];

        yield 'route parameter equal to a static segment' => ['/token/token', '/*REDACTED*/*REDACTED*'];

        yield 'non-sensitive route parameter' => ['/lookup/some-value', '/lookup/some-value'];

        yield 'non-sanitizable route parameter' => ['/lookup/%7B%22amount%22:1e400%7D', '/lookup/[omitted]'];

        yield 'unmatched path with key segment' => ['/some/token/some-token', '/some/token/*REDACTED*'];

        yield 'unmatched path with key=value segment' => ['/some/token=some-token', '/some/token=*REDACTED*'];

        yield 'unmatched path with card number' => ['/some/4111%201111%201111%201111', '/some/411111*REDACTED*1111'];
    }

    /**
     * @see testOnKernelTerminateSucceedsWithTopLevelNumberBody
     */
    public static function provideTopLevelNumberBodies(): iterable
    {
        yield 'card number' => ['4111111111111111', '"411111*REDACTED*1111"'];

        yield 'card number in exponent notation' => ['4.111111111111111e15', '"411111*REDACTED*1111"'];

        yield 'decimal number' => ['1.5', '1.5'];

        yield 'long number' => ['100000000000', '100000000000'];
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
        self::assertSame('response', $requestContext['route']);
        self::assertSame([], $requestContext['route_params']);
        self::assertSame('/response', $requestContext['uri']);
        self::assertSame(
            '{"card_token":"*REDACTED*","float_note":"411111*REDACTED*1111","name":"John",'
            . '"note":"411111*REDACTED*1111"}',
            $responseContext['body']
        );
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

    public function testOnKernelTerminateSucceedsAndDoesNotLogFormBodyWithAlteredFieldNames(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create(
            uri: '/response',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            ],
            content: 'name=John&%20password=some-password'
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $requestContext = self::getSingleRecord()->context['request'];
        self::assertIsArray($requestContext);
        self::assertNull($requestContext['body']);
        self::assertTrue($requestContext['body_omitted']);
        self::assertFalse($requestContext['body_truncated']);
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

    public function testOnKernelTerminateSucceedsAndDoesNotReadBodyWithTooBigContentLength(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create(
            uri: '/response',
            method: 'POST',
            server: [
                'CONTENT_LENGTH' => '999999',
                'CONTENT_TYPE' => 'application/json',
            ],
            content: '{"name":"John"}'
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

    public function testOnKernelTerminateSucceedsAndRunsRemainingListenersWhenLoggerFails(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create(
            uri: '/response',
            server: [
                'HTTP_' . \strtoupper(\str_replace('-', '_', FailingProcessor::HEADER)) => '1',
            ]
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        self::assertSame([], self::getRecords());
        self::assertTrue($request->attributes->get(LastTerminateListener::ATTRIBUTE));
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

    public function testOnKernelTerminateSucceedsWithBodyAlreadyReadAsStream(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create(
            uri: '/response',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_' . \strtoupper(\str_replace('-', '_', ResponseController::READ_BODY_AS_STREAM_HEADER)) => '1',
            ],
            content: '{"password":"some-password"}'
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $requestContext = self::getSingleRecord()->context['request'];
        self::assertIsArray($requestContext);
        self::assertSame('{"password":"*REDACTED*"}', $requestContext['body']);
        self::assertTrue($request->attributes->get(LastTerminateListener::ATTRIBUTE));
    }

    public function testOnKernelTerminateSucceedsWithEscapedJsonStringBody(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create(
            uri: '/response',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
            ],
            content: '"\u0034' . \str_repeat('\u0031', 15) . '"'
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $requestContext = self::getSingleRecord()->context['request'];
        self::assertIsArray($requestContext);
        self::assertSame('"411111*REDACTED*1111"', $requestContext['body']);
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
                . '&payment.secret=some-secret'
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $requestContext = self::getSingleRecord()->context['request'];
        self::assertIsArray($requestContext);
        self::assertSame(
            'user[name]=John&user[password]=*REDACTED*&password=*REDACTED*&payment.secret=*REDACTED*',
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

    public function testOnKernelTerminateSucceedsWithNonSanitizableMetadata(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create(
            uri: '/response?filter=%7B%22amount%22:1e400%7D&name=John',
            server: [
                'HTTP_X_CORRELATION_ID' => 'some-correlation-id',
                'HTTP_X_REQUEST_ID' => '{"amount":1e400}',
                'HTTP_X_SOME_HEADER' => '{"amount":1e400}',
            ]
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $record = self::getSingleRecord();
        $requestContext = $record->context['request'];
        self::assertIsArray($requestContext);
        self::assertIsArray($requestContext['headers']);
        self::assertSame('some-correlation-id', $record->context['correlation_id']);
        self::assertNull($record->context['request_id']);
        self::assertSame(['some-correlation-id'], $requestContext['headers']['x-correlation-id']);
        self::assertNull($requestContext['headers']['x-request-id']);
        self::assertNull($requestContext['headers']['x-some-header']);
        self::assertSame(
            [
                'filter' => null,
                'name' => 'John',
            ],
            $requestContext['query']
        );
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

    public function testOnKernelTerminateSucceedsWithNumericCardNumberBody(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create(
            uri: '/response',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
            ],
            content: '{"id":100000000000,"items":[{"note":4111111111111111}],"amount":1.5,'
                . '"float_note":4111111111111111.0,"exponent_note":4.111111111111111e15}'
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $requestContext = self::getSingleRecord()->context['request'];
        self::assertIsArray($requestContext);
        self::assertSame(
            '{"id":100000000000,"items":[{"note":"411111*REDACTED*1111"}],"amount":1.5,'
            . '"float_note":"411111*REDACTED*1111","exponent_note":"411111*REDACTED*1111"}',
            $requestContext['body']
        );
    }

    public function testOnKernelTerminateSucceedsWithSanitizedDottedQueryKey(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create('/response?payment.secret=some-secret&name=John');

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $requestContext = self::getSingleRecord()->context['request'];
        self::assertIsArray($requestContext);
        self::assertSame(
            [
                'payment.secret' => '*REDACTED*',
                'name' => 'John',
            ],
            $requestContext['query']
        );
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
        self::assertSame(
            \sprintf('Request: "GET %s" -> %d', $expectedPath, $response->getStatusCode()),
            $record->message
        );
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

    public function testOnKernelTerminateSucceedsWithSanitizedRouteParams(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create('/token/some-token');

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $requestContext = self::getSingleRecord()->context['request'];
        self::assertIsArray($requestContext);
        self::assertSame('token', $requestContext['route']);
        self::assertSame(
            [
                'token' => '*REDACTED*',
            ],
            $requestContext['route_params']
        );
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

    public function testOnKernelTerminateSucceedsWithTruncatedMultibyteBody(): void
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
                'description' => 'a' . \str_repeat('я', 1024),
            ], \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_UNICODE)
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $requestContext = self::getSingleRecord()->context['request'];
        self::assertIsArray($requestContext);
        self::assertIsString($requestContext['body']);
        self::assertTrue($requestContext['body_truncated']);
        self::assertSame(1023, \strlen($requestContext['body']));
        self::assertTrue(\mb_check_encoding($requestContext['body'], 'UTF-8'));
    }

    #[DataProvider('provideTopLevelNumberBodies')]
    public function testOnKernelTerminateSucceedsWithTopLevelNumberBody(string $content, string $expectedBody): void
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
        self::assertSame($expectedBody, $requestContext['body']);
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
