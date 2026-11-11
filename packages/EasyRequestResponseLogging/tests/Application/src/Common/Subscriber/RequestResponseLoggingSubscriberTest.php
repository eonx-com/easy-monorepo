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
     * @see testOnKernelTerminateSucceedsWithNumericCardNumberInJsonStringBody
     */
    public static function provideNumericCardNumberInJsonStringBodies(): iterable
    {
        yield 'json field' => [
            'application/json',
            '{"payload":"{\\"note\\":4111111111111111}"}',
            '{"payload":"{\\"note\\":\\"411111*REDACTED*1111\\"}"}',
        ];

        yield 'json field with nested json string' => [
            'application/json',
            '{"payload":"{\\"inner\\":\\"{\\\\\\"note\\\\\\":4111111111111111}\\"}"}',
            '{"payload":"{\\"inner\\":\\"{\\\\\\"note\\\\\\":\\\\\\"411111*REDACTED*1111\\\\\\"}\\"}"}',
        ];

        yield 'top-level json string' => [
            'application/json',
            '"{\\"note\\":4111111111111111}"',
            '"{\\"note\\":\\"411111*REDACTED*1111\\"}"',
        ];

        yield 'form field' => [
            'application/x-www-form-urlencoded',
            'payload=%7B%22note%22%3A4111111111111111%7D',
            'payload={"note":"411111*REDACTED*1111"}',
        ];
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

        yield 'unmatched path with card number' => ['/some/4111-1111-1111-1111', '/some/411111*REDACTED*1111'];

        yield 'unmatched path with card number with encoded spaces' => ['/some/4111%201111%201111%201111', '[omitted]'];

        yield 'unmatched path with encoded ampersand' => ['/some/token/first-secret%26second-secret', '[omitted]'];

        yield 'unmatched path with encoded question mark' => ['/some/token/first%3Fsecond', '[omitted]'];

        yield 'unmatched path with encoded hash' => ['/some/token/first%23second', '[omitted]'];

        yield 'unmatched path with encoded comma' => ['/some/token=first%2Csecond', '[omitted]'];

        yield 'unmatched path with encoded semicolon' => ['/some/token=first%3Bsecond', '[omitted]'];

        yield 'unmatched path with encoded space' => ['/some/token=first%20second', '[omitted]'];

        yield 'unmatched path with encoded slash' => ['/some/token/first%2Fsecond', '[omitted]'];

        yield 'unmatched path with slash after key value' => [
            '/some/token/first/second',
            '/some/token/*REDACTED*/second',
        ];

        yield 'unmatched path with secret starting with encoded ampersand' => [
            '/some/token/%26second-secret',
            '[omitted]',
        ];

        yield 'unmatched path with secret starting with encoded slash' => ['/some/token/%2Fsecond-secret', '[omitted]'];

        yield 'unmatched path with key containing encoded space' => [
            '/some/api%20key=first%26second-secret',
            '[omitted]',
        ];

        yield 'unmatched path with bracketed key' => ['/some/%5Btoken%5D=first%2Csecond-secret', '[omitted]'];

        yield 'unmatched path with literal delimiter' => ['/some/token=first,second-secret', '[omitted]'];

        yield 'unmatched path with encoded delimiters and no key' => ['/some/first%2Csecond', '[omitted]'];
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
        self::assertIsArray($responseContext['headers']);
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
        self::assertSame('/response', $requestContext['path']);
        self::assertSame(
            '{"card_token":"*REDACTED*","float_note":"411111*REDACTED*1111","name":"John",'
            . '"note":"411111*REDACTED*1111"}',
            $responseContext['body']
        );
        self::assertFalse($responseContext['body_omitted']);
        self::assertFalse($responseContext['body_truncated']);
        self::assertGreaterThan(0.0, $responseContext['duration_ms']);
        self::assertSame(['application/json'], $responseContext['headers']['content-type']);
        self::assertSame('*REDACTED*', $responseContext['headers']['set-cookie']);
        self::assertSame(200, $responseContext['status_code']);
    }

    public function testOnKernelTerminateSucceedsAndDoesNotCopyAlreadyReadBodyIntoStream(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $requestClass = new class extends Request {
            private bool $contentReadAsResource = false;

            /**
             * @return resource|string
             */
            public function getContent(bool $asResource = false)
            {
                $this->contentReadAsResource = $this->contentReadAsResource || $asResource;

                return parent::getContent($asResource);
            }

            public function isContentReadAsResource(): bool
            {
                return $this->contentReadAsResource;
            }
        };
        $request = $requestClass::create(
            uri: '/response',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
            ],
            content: '{"password":"some-password"}'
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $requestContext = self::getSingleRecord()->context['request'];
        self::assertIsArray($requestContext);
        self::assertSame('{"password":"*REDACTED*"}', $requestContext['body']);
        self::assertFalse($request->isContentReadAsResource());
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

    public function testOnKernelTerminateSucceedsAndDoesNotReadTooBigBodyWithoutContentLength(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $content = \json_encode([
            'password' => 'some-password',
            'description' => \str_repeat('a', 5000),
        ], \JSON_THROW_ON_ERROR);
        /** @var resource $resource */
        $resource = \fopen('php://temp', 'r+b');
        \fwrite($resource, $content);
        $request = Request::create(
            uri: '/response',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
            ],
            content: $resource
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $requestContext = self::getSingleRecord()->context['request'];
        self::assertIsArray($requestContext);
        self::assertNull($requestContext['body']);
        self::assertTrue($requestContext['body_omitted']);
        self::assertFalse($requestContext['body_truncated']);
        // Only max_raw_body_size + 1 bytes are read to detect a too big body
        self::assertSame(4097, \ftell($resource));
        self::assertSame($content, $request->getContent());
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

    public function testOnKernelTerminateSucceedsWithDurationFromRequestTimeFloat(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create(
            uri: '/response',
            server: [
                'REQUEST_TIME_FLOAT' => \microtime(true) - 5,
            ]
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $responseContext = self::getSingleRecord()->context['response'];
        self::assertIsArray($responseContext);
        self::assertGreaterThanOrEqual(5000.0, $responseContext['duration_ms']);
    }

    public function testOnKernelTerminateSucceedsWithDurationWhenResponseIsReturnedEarly(): void
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
        self::assertIsFloat($responseContext['duration_ms']);
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

    public function testOnKernelTerminateSucceedsWithNullDurationWhenRequestTimeFloatIsMissing(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create(uri: '/response');
        $request->server->remove('REQUEST_TIME_FLOAT');

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

    #[DataProvider('provideNumericCardNumberInJsonStringBodies')]
    public function testOnKernelTerminateSucceedsWithNumericCardNumberInJsonStringBody(
        string $contentType,
        string $content,
        string $expectedBody,
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
        self::assertSame($expectedBody, $requestContext['body']);
    }

    public function testOnKernelTerminateSucceedsWithNumericCardNumberInJsonStringQueryAndHeaders(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create(
            uri: '/response?payload=%7B%22note%22%3A4111111111111111%7D'
                . '&escaped=%7B%5C%22note%5C%22%3A4111111111111111%7D&name=John',
            server: [
                'HTTP_X_PAYLOAD' => '{"note":4111111111111111}',
            ]
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $requestContext = self::getSingleRecord()->context['request'];
        self::assertIsArray($requestContext);
        self::assertIsArray($requestContext['headers']);
        self::assertSame(
            [
                'payload' => '{"note":"411111*REDACTED*1111"}',
                'escaped' => '{"note":"411111*REDACTED*1111"}',
                'name' => 'John',
            ],
            $requestContext['query']
        );
        self::assertSame(['{"note":"411111*REDACTED*1111"}'], $requestContext['headers']['x-payload']);
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
        self::assertSame($expectedPath, $requestContext['path']);
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

    public function testOnKernelTerminateSucceedsWithSanitizedRequestIdsHoldingNumericCardNumberInJson(): void
    {
        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = self::bootKernel();
        $request = Request::create(
            uri: '/response',
            server: [
                'HTTP_X_CORRELATION_ID' => '{"note":5555555555554444}',
                'HTTP_X_REQUEST_ID' => '{"note":4111111111111111}',
            ]
        );

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $record = self::getSingleRecord();
        self::assertSame('{"note":"555555*REDACTED*4444"}', $record->context['correlation_id']);
        self::assertSame('{"note":"411111*REDACTED*1111"}', $record->context['request_id']);
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

    /**
     * @return \Monolog\LogRecord[]
     */
    private static function getRecords(): array
    {
        return \array_values(\array_filter(
            LogCollectorProcessor::getRecords(),
            static fn(LogRecord $record): bool => $record->channel === BundleParam::LogChannel->value
        ));
    }

    private static function getSingleRecord(): LogRecord
    {
        $records = self::getRecords();

        self::assertCount(1, $records);

        return $records[0];
    }
}
