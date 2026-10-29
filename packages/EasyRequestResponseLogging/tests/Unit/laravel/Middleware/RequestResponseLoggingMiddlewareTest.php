<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Tests\Unit\Laravel\Middleware;

use EonX\EasyRequestResponseLogging\Bundle\Enum\ConfigServiceId;
use EonX\EasyRequestResponseLogging\Common\Logger\RequestResponseLogger;
use EonX\EasyRequestResponseLogging\Laravel\Middleware\RequestResponseLoggingMiddleware;
use EonX\EasyRequestResponseLogging\Tests\Unit\Laravel\AbstractLumenTestCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Laravel\Lumen\Application;
use Monolog\LogRecord;
use Psr\Log\AbstractLogger;
use RuntimeException;
use stdClass;
use Stringable;

final class RequestResponseLoggingMiddlewareTest extends AbstractLumenTestCase
{
    public function testTerminateSucceeds(): void
    {
        $app = $this->createApplicationWithRoutes();
        $request = Request::create(
            uri: '/token/some-token?password=some-password&name=John',
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer some-token',
            ],
            content: '{"password":"some-password","name":"John"}'
        );

        $response = $app->handle($request);

        self::assertSame(200, $response->getStatusCode());
        $record = $this->getSingleRecord();
        self::assertSame('Request: "POST /token/*REDACTED*" -> 200', $record->message);
        $requestContext = $record->context['request'];
        self::assertIsArray($requestContext);
        self::assertSame('{"password":"*REDACTED*","name":"John"}', $requestContext['body']);
        $headers = $requestContext['headers'];
        self::assertIsArray($headers);
        self::assertSame('*REDACTED*', $headers['authorization']);
        self::assertSame(
            [
                'password' => '*REDACTED*',
                'name' => 'John',
            ],
            $requestContext['query']
        );
        self::assertSame('token', $requestContext['route']);
        self::assertSame(
            [
                'token' => '*REDACTED*',
            ],
            $requestContext['route_params']
        );
        $responseContext = $record->context['response'];
        self::assertIsArray($responseContext);
        self::assertSame('{"password":"*REDACTED*"}', $responseContext['body']);
        self::assertIsFloat($responseContext['duration_ms']);
        self::assertIsString($record->context['request_id']);
        self::assertIsString($record->context['correlation_id']);
    }

    public function testTerminateSucceedsAndDoesNotLogDoNotLogPath(): void
    {
        $app = $this->createApplicationWithRoutes();

        $app->handle(Request::create('/ping'));

        self::assertSame([], $this->logHandler->getRecords());
    }

    public function testTerminateSucceedsAndSwallowsLoggerFailure(): void
    {
        $app = $this->createApplicationWithRoutes();
        $app->instance(ConfigServiceId::Logger->value, new class() extends AbstractLogger {
            public function log($level, string|Stringable $message, array $context = []): void
            {
                throw new RuntimeException('Logger failure.');
            }
        });

        $response = $app->handle(Request::create('/ping-free'));

        self::assertSame(200, $response->getStatusCode());
    }

    public function testTerminateSucceedsWithEncodedRouteParameter(): void
    {
        $app = $this->createApplicationWithRoutes();

        $app->handle(Request::create(uri: '/token/some%2Dtoken', method: 'POST'));

        $record = $this->getSingleRecord();
        $requestContext = $record->context['request'];
        self::assertIsArray($requestContext);
        self::assertSame('Request: "POST /token/*REDACTED*" -> 200', $record->message);
        self::assertSame('/token/*REDACTED*', $requestContext['path']);
        self::assertSame(
            [
                'token' => '*REDACTED*',
            ],
            $requestContext['route_params']
        );
    }

    public function testTerminateSucceedsWithExceptionResponse(): void
    {
        $app = $this->createApplicationWithRoutes();

        $response = $app->handle(Request::create('/exception'));

        self::assertSame(500, $response->getStatusCode());
        $record = $this->getSingleRecord();
        self::assertSame('Request: "GET /exception" -> 500', $record->message);
        $responseContext = $record->context['response'];
        self::assertIsArray($responseContext);
        self::assertIsFloat($responseContext['duration_ms']);
    }

    public function testTerminateSucceedsWithLaravelRouteOriginalParameters(): void
    {
        $app = $this->createApplication();
        $request = Request::create('/lookup/4111111111111111');
        $route = (new Route('GET', '/lookup/{value}', []))->name('lookup');
        $route->bind($request);
        // Route model binding replaces the parameter, the original raw value must be logged instead
        $route->setParameter('value', new stdClass());
        $request->setRouteResolver(static fn (): Route => $route);
        $middleware = new RequestResponseLoggingMiddleware($app->make(RequestResponseLogger::class));

        $middleware->terminate($request, new JsonResponse([]));

        $requestContext = $this->getSingleRecord()
->context['request'];

        self::assertIsArray($requestContext);
        self::assertSame('/lookup/411111*REDACTED*1111', $requestContext['path']);
        self::assertSame('lookup', $requestContext['route']);
        self::assertSame(
            [
                'value' => '411111*REDACTED*1111',
            ],
            $requestContext['route_params']
        );
    }

    public function testTerminateSucceedsWithUnmatchedPath(): void
    {
        $app = $this->createApplicationWithRoutes();

        $response = $app->handle(Request::create('/some/token=some-token'));

        self::assertSame(404, $response->getStatusCode());
        $requestContext = $this->getSingleRecord()
->context['request'];
        self::assertIsArray($requestContext);
        self::assertSame('/some/token=*REDACTED*', $requestContext['path']);
        self::assertNull($requestContext['route']);
        self::assertSame([], $requestContext['route_params']);
    }

    public function testTerminateSucceedsWithUnnamedRoute(): void
    {
        $app = $this->createApplicationWithRoutes();

        $app->handle(Request::create('/lookup/4111111111111111'));

        $requestContext = $this->getSingleRecord()
->context['request'];

        self::assertIsArray($requestContext);
        self::assertSame('/lookup/411111*REDACTED*1111', $requestContext['path']);
        self::assertNull($requestContext['route']);
        self::assertSame(
            [
                'value' => '411111*REDACTED*1111',
            ],
            $requestContext['route_params']
        );
    }

    private function createApplicationWithRoutes(): Application
    {
        $app = $this->createApplication();
        // Lumen binds route closures to the router, so they cannot be static
        $responder = fn (): JsonResponse => new JsonResponse(['password' => 'some-password']);
        $app->router->post('/token/{token}', [
            'as' => 'token',
            $responder,
        ]);
        $app->router->get('/lookup/{value}', $responder);
        $app->router->get('/ping', $responder);
        $app->router->get('/ping-free', $responder);
        $app->router->get('/exception', function (): never {
            throw new RuntimeException('Some exception.');
        });

        return $app;
    }

    private function getSingleRecord(): LogRecord
    {
        $records = $this->logHandler->getRecords();
        self::assertCount(1, $records);

        return $records[0];
    }
}
