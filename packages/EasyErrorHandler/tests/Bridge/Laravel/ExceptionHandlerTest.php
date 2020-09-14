<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Laravel;

use EonX\EasyErrorHandler\Tests\Stubs\BaseExceptionStub;
use EonX\EasyErrorHandler\Tests\Stubs\ValidationExceptionStub;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;

final class ExceptionHandlerTest extends AbstractLaravelTestCase
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
                self::assertSame('easy-error-handler::messages.default_user_message', $content['message']);
            },
        ];

        yield 'Returns extended response on debug' => [
            new Request(),
            new \Exception(),
            static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                self::assertSame(['code', 'exception', 'message', 'time'], \array_keys($content));
                self::assertSame(['class', 'file', 'line', 'message', 'trace'], \array_keys($content['exception']));
            },
            ['easy-error-handler' => ['use_extended_response' => true]],
        ];

        yield 'Returns messages as is if translations are absent' => [
            new Request(),
            (new BaseExceptionStub('Exception message'))->setUserMessage('User-friendly error message'),
            static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                self::assertSame('User-friendly error message', $content['message']);
                self::assertSame('Exception message', $content['exception']['message']);
            },
            ['easy-error-handler' => ['use_extended_response' => true]],
        ];

        yield 'Returns message with params' => [
            new Request(),
            (new BaseExceptionStub('test.exception_message'))
                ->setMessageParams(['param' => 'foo'])
                ->setUserMessage('test.user_message')
                ->setUserMessageParams(['param' => 'bar']),
            static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                self::assertSame('Exception message with foo', $content['exception']['message']);
                self::assertSame('User-friendly error message with bar', $content['message']);
            },
            ['easy-error-handler' => ['use_extended_response' => true]],
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
                $content = \json_decode((string)$response->getContent(), true);
                self::assertArrayHasKey('sub_code', $content);
                self::assertSame(123456, $content['sub_code']);
            },
        ];

        yield 'Response with time in zulu format' => [
            new Request(),
            new \Exception(),
            static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                self::assertRegExp('/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}Z$/', $content['time']);
            },
        ];

        yield 'Response with validation errors' => [
            new Request(),
            (new ValidationExceptionStub())->setErrors(['foo' => 'bar']),
            static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                self::assertArrayHasKey('violations', $content);
                self::assertSame(['foo' => 'bar'], $content['violations']);
            },
        ];

        yield 'Short response' => [
            new Request(),
            new \Exception(),
            static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                self::assertSame(['code', 'message', 'time'], \array_keys($content));
            },
        ];

        yield 'Short response with sub_code' => [
            new Request(),
            (new BaseExceptionStub())->setSubCode(123),
            static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                self::assertArrayHasKey('sub_code', $content);
                self::assertSame(123, $content['sub_code']);
            },
        ];

        yield 'Short response with violations' => [
            new Request(),
            (new ValidationExceptionStub())->setErrors(['foo' => ['bar']]),
            static function (Response $response): void {
                $content = \json_decode((string)$response->getContent(), true);
                self::assertArrayHasKey('violations', $content);
                self::assertSame(['foo' => ['bar']], $content['violations']);
            },
        ];
    }

    public function testRenderForConsoleDoesNotShowTranslationIfItEqualsToOriginalMessage(): void
    {
        $handler = $this->getApplication()->make(ExceptionHandler::class);
        $output = new BufferedOutput();

        $handler->renderForConsole($output, new \Exception('test.message'));

        self::assertStringNotContainsString('Translated exception message', $output->fetch());
    }

    public function testRenderForConsoleShowsExceptionWithTranslation(): void
    {
        $message = 'Exception message.';
        $app = $this->getApplication();
        $translator = $app->make('translator');
        $translator->addLines(['test.message' => $message], $translator->getLocale());
        $handler = $app->make(ExceptionHandler::class);
        $output = new BufferedOutput();
        $expectedMessage = \sprintf('Translated exception message: %s', $message);

        $handler->renderForConsole($output, new BaseExceptionStub('test.message'));

        self::assertStringContainsString($expectedMessage, $output->fetch());
    }

    public function testRenderForConsoleShowsExceptionWithoutTranslation(): void
    {
        $message = 'Exception message.';
        $handler = $this->getApplication()->make(ExceptionHandler::class);
        $output = new BufferedOutput();

        $handler->renderForConsole($output, new \Exception($message));

        self::assertStringContainsString($message, $output->fetch());
        self::assertStringNotContainsString('Translated exception message', $output->fetch());
    }

    public function testRenderForConsoleShowsValidationExceptionWithFailures(): void
    {
        $handler = $this->getApplication()->make(ExceptionHandler::class);
        $output = new BufferedOutput();
        $exception = (new ValidationExceptionStub())->setErrors(['property' => ['Property must not be null']]);

        $handler->renderForConsole($output, $exception);

        $consoleOutput = $output->fetch();
        self::assertStringContainsString('Validation Failures:', $consoleOutput);
        self::assertStringContainsString('property - "Property must not be null"', $consoleOutput);
    }

    public function testRenderForConsoleShowsValidationExceptionWithoutFailures(): void
    {
        $handler = $this->getApplication()->make(ExceptionHandler::class);
        $output = new BufferedOutput();

        $handler->renderForConsole($output, new ValidationExceptionStub());

        $consoleOutput = $output->fetch();
        self::assertStringContainsString('Validation Failures:', $consoleOutput);
        self::assertStringContainsString('No validation errors in exception', $consoleOutput);
    }

    /**
     * @param null|mixed[] $config
     * @param null|mixed[] $translations
     *
     * @dataProvider providerTestRenderWithDefaultBuilders
     */
    public function testRenderWithDefaultBuilders(
        Request $request,
        \Exception $exception,
        callable $assertResponse,
        ?array $config = null,
        ?array $translations = null
    ): void {
        $app = $this->getApplication($config);
        $handler = $app->make(ExceptionHandler::class);

        if ($translations !== null) {
            $translator = $app->make('translator');
            $translator->addLines($translations, $translator->getLocale());
        }

        $assertResponse($handler->render($request, $exception));
    }
}
