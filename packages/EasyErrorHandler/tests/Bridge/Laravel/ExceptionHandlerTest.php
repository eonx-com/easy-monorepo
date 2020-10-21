<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Laravel;

use EonX\EasyErrorHandler\Tests\Stubs\BaseExceptionStub;
use EonX\EasyErrorHandler\Tests\Stubs\ValidationExceptionStub;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\Console\Output\BufferedOutput;

final class ExceptionHandlerTest extends AbstractLaravelTestCase
{
    public function testRenderForConsoleDoesNotShowTranslationIfItEqualsToOriginalMessage(): void
    {
        $handler = $this->getApplication()
            ->make(ExceptionHandler::class);
        $output = new BufferedOutput();

        $handler->renderForConsole($output, new \Exception('test.message'));

        self::assertStringNotContainsString('Translated exception message', $output->fetch());
    }

    public function testRenderForConsoleShowsExceptionWithTranslation(): void
    {
        $message = 'Exception message.';
        $app = $this->getApplication();
        $translator = $app->make('translator');
        $translator->addLines([
            'test.message' => $message,
        ], $translator->getLocale());
        $handler = $app->make(ExceptionHandler::class);
        $output = new BufferedOutput();
        $expectedMessage = \sprintf('Translated exception message: %s', $message);

        $handler->renderForConsole($output, new BaseExceptionStub('test.message'));

        self::assertStringContainsString($expectedMessage, $output->fetch());
    }

    public function testRenderForConsoleShowsExceptionWithoutTranslation(): void
    {
        $message = 'Exception message.';
        $handler = $this->getApplication()
            ->make(ExceptionHandler::class);
        $output = new BufferedOutput();

        $handler->renderForConsole($output, new \Exception($message));

        self::assertStringContainsString($message, $output->fetch());
        self::assertStringNotContainsString('Translated exception message', $output->fetch());
    }

    public function testRenderForConsoleShowsValidationExceptionWithFailures(): void
    {
        $handler = $this->getApplication()
            ->make(ExceptionHandler::class);
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
        $handler = $this->getApplication()
            ->make(ExceptionHandler::class);
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
