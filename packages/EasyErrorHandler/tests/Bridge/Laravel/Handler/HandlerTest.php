<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Laravel\Handler;

use DomainException;
use EoneoPay\ApiFormats\EncoderGuesser;
use EoneoPay\ApiFormats\Encoders\XmlEncoder;
use EoneoPay\ApiFormats\External\Libraries\Psr7\Psr7Factory;
use EonX\EasyErrorHandler\Bridge\Laravel\Handler\Handler;
use EonX\EasyErrorHandler\Tests\AbstractTestCase;
use EonX\EasyErrorHandler\Tests\Stubs\BaseExceptionStub;
use EonX\EasyErrorHandler\Tests\Stubs\LoggerStub;
use EonX\EasyErrorHandler\Tests\Stubs\ValidationExceptionStub;
use EonX\EasyLogging\Interfaces\LoggerInterface;
use Exception;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Http\Request;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use LogicException;
use Mockery\MockInterface;
use RuntimeException;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @covers \EonX\EasyErrorHandler\Bridge\Laravel\Handler\Handler
 */
final class HandlerTest extends AbstractTestCase
{
    /**
     * @return mixed[]
     *
     * @see testShouldReportReturnsExpectedResult
     */
    public function provideExceptionsForShouldReport(): array
    {
        return [
            'Exception is in the dont report list' => [
                'dontReport' => [LogicException::class],
                'exception' => new LogicException(),
                'shouldReport' => false,
            ],
            'Parent exception is in the dont report list' => [
                'dontReport' => [LogicException::class],
                'exception' => new DomainException(),
                'shouldReport' => false,
            ],
            'Exception is not in the dont report list' => [
                'dontReport' => [LogicException::class],
                'exception' => new RuntimeException(),
                'shouldReport' => true,
            ],
        ];
    }

    public function testRenderForConsoleDoesNotShowTranslationIfItEqualsToOriginalMessage(): void
    {
        $handler = new Handler(
            new EncoderGuesser([]),
            new Psr7Factory(),
            new ConfigRepository(),
            $this->createTranslator(),
            new LoggerStub()
        );
        $output = new BufferedOutput();

        $handler->renderForConsole($output, new \Exception('test.message'));

        self::assertStringNotContainsString('Translated exception message', $output->fetch());
    }

    public function testRenderForConsoleShowsExceptionWithTranslation(): void
    {
        $message = 'Exception message.';
        $handler = new Handler(
            new EncoderGuesser([]),
            new Psr7Factory(),
            new ConfigRepository(),
            $this->createTranslator(['message' => $message]),
            new LoggerStub()
        );
        $output = new BufferedOutput();
        $expectedMessage = \sprintf('Translated exception message: %s', $message);

        $handler->renderForConsole($output, new BaseExceptionStub('test.message'));

        self::assertStringContainsString($expectedMessage, $output->fetch());
    }

    public function testRenderForConsoleShowsExceptionWithoutTranslation(): void
    {
        $message = 'Exception message.';
        $handler = new Handler(
            new EncoderGuesser([]),
            new Psr7Factory(),
            new ConfigRepository(),
            $this->createTranslator(),
            new LoggerStub()
        );
        $output = new BufferedOutput();

        $handler->renderForConsole($output, new \Exception($message));

        self::assertStringContainsString($message, $output->fetch());
        self::assertStringNotContainsString('Translated exception message', $output->fetch());
    }

    public function testRenderForConsoleShowsValidationExceptionWithFailures(): void
    {
        $handler = new Handler(
            new EncoderGuesser([]),
            new Psr7Factory(),
            new ConfigRepository(),
            $this->createTranslator(),
            new LoggerStub()
        );
        $output = new BufferedOutput();
        $exception = (new ValidationExceptionStub())->setErrors([
            'property' => ['Property must not be null'],
        ]);

        $handler->renderForConsole($output, $exception);

        $consoleOutput = $output->fetch();
        self::assertStringContainsString('Validation Failures:', $consoleOutput);
        self::assertStringContainsString('property - "Property must not be null"', $consoleOutput);
    }

    public function testRenderForConsoleShowsValidationExceptionWithoutFailures(): void
    {
        $handler = new Handler(
            new EncoderGuesser([]),
            new Psr7Factory(),
            new ConfigRepository(),
            $this->createTranslator(),
            new LoggerStub()
        );
        $output = new BufferedOutput();

        $handler->renderForConsole($output, new ValidationExceptionStub());

        $consoleOutput = $output->fetch();
        self::assertStringContainsString('Validation Failures:', $consoleOutput);
        self::assertStringContainsString('No validation errors in exception', $consoleOutput);
    }

    public function testRenderReturnsDefaultUserMessage(): void
    {
        $handler = new Handler(
            new EncoderGuesser([]),
            new Psr7Factory(),
            new ConfigRepository(),
            $this->createTranslator(),
            new LoggerStub()
        );

        $response = $handler->render(new Request(), new \Exception());

        $content = \json_decode((string)$response->getContent(), true);
        self::assertSame('easy-error-handler::messages.default_user_message', $content['message']);
    }

    public function testRenderReturnsExtendedResponseOnDebug(): void
    {
        $handler = new Handler(
            new EncoderGuesser([]),
            new Psr7Factory(),
            $this->createConfigRepositoryWithExtendedErrorResponse(),
            $this->createTranslator(),
            new LoggerStub()
        );

        $response = $handler->render(new Request(), new \Exception());

        $content = \json_decode((string)$response->getContent(), true);
        self::assertSame(['code', 'exception', 'message', 'time'], \array_keys($content));
        self::assertSame(['class', 'file', 'line', 'message', 'trace'], \array_keys($content['exception']));
    }

    public function testRenderReturnsMessagesAsIsIfTranslationsAreAbsent(): void
    {
        $translations = [];
        $handler = new Handler(
            new EncoderGuesser([]),
            new Psr7Factory(),
            $this->createConfigRepositoryWithExtendedErrorResponse(),
            $this->createTranslator($translations),
            new LoggerStub()
        );
        $message = 'Exception message';
        $userMessage = 'User-friendly error message';
        $exception = (new BaseExceptionStub($message))->setUserMessage($userMessage);

        $response = $handler->render(new Request(), $exception);

        $content = \json_decode((string)$response->getContent(), true);
        self::assertSame($userMessage, $content['message']);
        self::assertSame($message, $content['exception']['message']);
    }

    public function testRenderReturnsMessagesWithParams(): void
    {
        $translations = [
            'exception_message' => 'Exception message with :param',
            'user_message' => 'User-friendly error message with :param',
        ];
        $handler = new Handler(
            new EncoderGuesser([]),
            new Psr7Factory(),
            $this->createConfigRepositoryWithExtendedErrorResponse(),
            $this->createTranslator($translations),
            new LoggerStub()
        );
        $exception = (new BaseExceptionStub('test.exception_message'))
            ->setMessageParams(['param' => 'foo'])
            ->setUserMessage('test.user_message')
            ->setUserMessageParams(['param' => 'bar']);

        $response = $handler->render(new Request(), $exception);

        $content = \json_decode((string)$response->getContent(), true);
        self::assertSame('Exception message with foo', $content['exception']['message']);
        self::assertSame('User-friendly error message with bar', $content['message']);
    }

    public function testRenderReturnsResponseFormattedByDefaultEncoderFromEncoderGuesser(): void
    {
        $defaultEncoder = XmlEncoder::class;
        $handler = new Handler(
            new EncoderGuesser([], $defaultEncoder),
            new Psr7Factory(),
            $this->createConfigRepositoryWithExtendedErrorResponse(),
            $this->createTranslator(),
            new LoggerStub()
        );

        $response = $handler->render(new Request(), new \Exception());

        /** @var string[] $xmlRows */
        $xmlRows = \explode("\n", (string)$response->getContent());
        self::assertSame('<?xml version="1.0" encoding="UTF-8"?>', $xmlRows[0]);
    }

    public function testRenderReturnsResponseFormattedByEncoderFromTheRequest(): void
    {
        $handler = new Handler(
            new EncoderGuesser([]),
            new Psr7Factory(),
            $this->createConfigRepositoryWithExtendedErrorResponse(),
            $this->createTranslator(),
            new LoggerStub()
        );
        $request = new Request();
        $request->attributes->set('_encoder', new XmlEncoder());

        $response = $handler->render($request, new \Exception());

        /** @var string[] $xmlRows */
        $xmlRows = \explode("\n", (string)$response->getContent());
        self::assertSame('<?xml version="1.0" encoding="UTF-8"?>', $xmlRows[0]);
    }

    public function testRenderReturnsResponseWith500StatusCodeByDefault(): void
    {
        $handler = new Handler(
            new EncoderGuesser([]),
            new Psr7Factory(),
            new ConfigRepository(),
            $this->createTranslator(),
            new LoggerStub()
        );

        $response = $handler->render(new Request(), new \Exception());

        self::assertSame(500, $response->getStatusCode());
    }

    public function testRenderReturnsResponseWithStatusCodeOfCodeAwareExceptionInterface(): void
    {
        $handler = new Handler(
            new EncoderGuesser([]),
            new Psr7Factory(),
            new ConfigRepository(),
            $this->createTranslator(),
            new LoggerStub()
        );
        $statusCode = 123;
        $exception = (new BaseExceptionStub())->setStatusCode($statusCode);

        $response = $handler->render(new Request(), $exception);

        self::assertSame($statusCode, $response->getStatusCode());
    }

    public function testRenderReturnsResponseWithSubcode(): void
    {
        $handler = new Handler(
            new EncoderGuesser([]),
            new Psr7Factory(),
            new ConfigRepository(),
            $this->createTranslator(),
            new LoggerStub()
        );
        $subCode = 123456;
        $exception = (new BaseExceptionStub())->setSubCode($subCode);

        $response = $handler->render(new Request(), $exception);

        $content = \json_decode((string)$response->getContent(), true);
        self::assertArrayHasKey('sub_code', $content);
        self::assertSame($subCode, $content['sub_code']);
    }

    public function testRenderReturnsResponseWithTimeInZuluFormat(): void
    {
        $handler = new Handler(
            new EncoderGuesser([]),
            new Psr7Factory(),
            new ConfigRepository(),
            $this->createTranslator(),
            new LoggerStub()
        );

        $response = $handler->render(new Request(), new \Exception());

        $content = \json_decode((string)$response->getContent(), true);
        self::assertRegExp('/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}Z$/', $content['time']);
    }

    public function testRenderReturnsResponseWithValidationErrors(): void
    {
        $handler = new Handler(
            new EncoderGuesser([]),
            new Psr7Factory(),
            new ConfigRepository(),
            $this->createTranslator(),
            new LoggerStub()
        );
        $errors = ['foo' => 'bar'];
        $exception = (new ValidationExceptionStub())->setErrors($errors);

        $response = $handler->render(new Request(), $exception);

        $content = \json_decode((string)$response->getContent(), true);
        self::assertArrayHasKey('violations', $content);
        self::assertSame($errors, $content['violations']);
    }

    public function testRenderReturnsShortResponse(): void
    {
        $handler = new Handler(
            new EncoderGuesser([]),
            new Psr7Factory(),
            new ConfigRepository(),
            $this->createTranslator(),
            new LoggerStub()
        );

        $response = $handler->render(new Request(), new \Exception());

        $content = \json_decode((string)$response->getContent(), true);
        self::assertSame(['code', 'message', 'time'], \array_keys($content));
    }

    public function testRenderReturnsShortResponseWithSubCode(): void
    {
        $handler = new Handler(
            new EncoderGuesser([]),
            new Psr7Factory(),
            new ConfigRepository(),
            $this->createTranslator(),
            new LoggerStub()
        );
        $subCode = 123;
        $exception = (new BaseExceptionStub())->setSubCode($subCode);

        $response = $handler->render(new Request(), $exception);

        $content = \json_decode((string)$response->getContent(), true);
        self::assertArrayHasKey('sub_code', $content);
        self::assertSame($subCode, $content['sub_code']);
    }

    public function testRenderReturnsShortResponseWithViolations(): void
    {
        $handler = new Handler(
            new EncoderGuesser([]),
            new Psr7Factory(),
            new ConfigRepository(),
            $this->createTranslator(),
            new LoggerStub()
        );
        $errors = [
            'foo' => ['bar'],
        ];
        $exception = (new ValidationExceptionStub())->setErrors($errors);

        $response = $handler->render(new Request(), $exception);

        $content = \json_decode((string)$response->getContent(), true);
        self::assertArrayHasKey('violations', $content);
        self::assertSame($errors, $content['violations']);
    }

    public function testReportDoesNotLogIfExceptionIsInDontReportList(): void
    {
        /** @var \EonX\EasyLogging\Interfaces\LoggerInterface $logger */
        $logger = $this->mock(LoggerInterface::class, static function (MockInterface $mock): void {
            $mock->shouldNotReceive('exception');
        });
        $handler = new Handler(
            new EncoderGuesser([]),
            new Psr7Factory(),
            new ConfigRepository(),
            $this->createTranslator(),
            $logger
        );
        $this->getPropertyAsPublic($handler, 'dontReport')->setValue($handler, [LogicException::class]);
        $exception = new LogicException();

        $handler->report($exception);

        // Otherwise PHPUnit warns that test does not perform any assertions
        self::assertTrue(true);
    }

    public function testReportLogsExceptionWithDefaultErrorLevel(): void
    {
        $exception = new \Exception();
        /** @var \EonX\EasyLogging\Interfaces\LoggerInterface $logger */
        $logger = $this->mock(
            LoggerInterface::class,
            static function (MockInterface $mock) use ($exception): void {
                $mock->shouldReceive('exception')
                    ->once()
                    ->with($exception, 'error');
            }
        );
        $handler = new Handler(
            new EncoderGuesser([]),
            new Psr7Factory(),
            new ConfigRepository(),
            $this->createTranslator(),
            $logger
        );

        $handler->report($exception);

        // Otherwise PHPUnit warns that test does not perform any assertions
        self::assertTrue(true);
    }

    public function testReportLogsExceptionWithLogLevelRetrievedFromLogLevelAwareException(): void
    {
        $exception = (new BaseExceptionStub())->setLogLevel('critical');
        /** @var \EonX\EasyLogging\Interfaces\LoggerInterface $logger */
        $logger = $this->mock(
            LoggerInterface::class,
            static function (MockInterface $mock) use ($exception): void {
                $mock->shouldReceive('exception')
                    ->once()
                    ->with($exception, 'critical');
            }
        );
        $handler = new Handler(
            new EncoderGuesser([]),
            new Psr7Factory(),
            new ConfigRepository(),
            $this->createTranslator(),
            $logger
        );

        $handler->report($exception);

        // Otherwise PHPUnit warns that test does not perform any assertions
        self::assertTrue(true);
    }

    /**
     * @param string[] $dontReport
     *
     * @dataProvider provideExceptionsForShouldReport
     */
    public function testShouldReportReturnsExpectedResult(
        array $dontReport,
        Exception $exception,
        bool $shouldReport
    ): void {
        $handler = new Handler(
            new EncoderGuesser([]),
            new Psr7Factory(),
            new ConfigRepository(),
            $this->createTranslator(),
            new LoggerStub()
        );
        $this->getPropertyAsPublic($handler, 'dontReport')->setValue($handler, $dontReport);

        $result = $handler->shouldReport($exception);

        self::assertSame($shouldReport, $result);
    }

    private function createConfigRepositoryWithExtendedErrorResponse(): ConfigRepository
    {
        return new ConfigRepository([
            'easy-error-handler' => [
                'use_extended_response' => true,
            ],
        ]);
    }

    /**
     * @param string[]|null $translations
     */
    private function createTranslator(?array $translations = null): Translator
    {
        $locale = 'en';

        $loader = (new ArrayLoader())->addMessages($locale, 'test', $translations ?? []);

        return new Translator($loader, $locale);
    }
}
