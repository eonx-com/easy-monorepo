<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Unit\Laravel;

use EonX\EasyErrorHandler\Tests\Stub\Exception\BaseExceptionStub;
use EonX\EasyErrorHandler\Tests\Stub\Exception\ValidationExceptionStub;
use EonX\EasyErrorHandler\Tests\Unit\Bundle\TestRenderWithDefaultBuilderDataProvider;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use Symfony\Component\Console\Output\BufferedOutput;

final class ErrorHandlerTest extends AbstractLaravelTestCase
{
    public function testRenderForConsoleDoesNotShowTranslationIfItEqualsToOriginalMessage(): void
    {
        /** @var \Illuminate\Contracts\Debug\ExceptionHandler $handler */
        $handler = $this->getApplication()
            ->make(ExceptionHandler::class);
        $output = new BufferedOutput();

        $handler->renderForConsole($output, new Exception('test.message'));
        $result = $output->fetch();

        self::assertStringNotContainsString('Translated exception message', $result);
    }

    public function testRenderForConsoleShowsExceptionWithTranslation(): void
    {
        $message = 'Exception message.';
        $app = $this->getApplication();
        /** @var \Illuminate\Translation\Translator $translator */
        $translator = $app->make('translator');
        $translator->addLines([
            'test.message' => $message,
        ], $translator->getLocale());
        /** @var \Illuminate\Contracts\Debug\ExceptionHandler $handler */
        $handler = $app->make(ExceptionHandler::class);
        $output = new BufferedOutput();
        $expectedMessage = \sprintf('Translated exception message: %s', $message);

        $handler->renderForConsole($output, new BaseExceptionStub('test.message'));
        $result = $output->fetch();

        self::assertStringContainsString($expectedMessage, $result);
    }

    public function testRenderForConsoleShowsExceptionWithoutTranslation(): void
    {
        $message = 'Exception message.';
        /** @var \Illuminate\Contracts\Debug\ExceptionHandler $handler */
        $handler = $this->getApplication()
            ->make(ExceptionHandler::class);
        $output = new BufferedOutput();

        $handler->renderForConsole($output, new Exception($message));
        $result = $output->fetch();

        self::assertStringContainsString($message, $result);
        self::assertStringNotContainsString('Translated exception message', $result);
    }

    public function testRenderForConsoleShowsValidationExceptionWithFailures(): void
    {
        /** @var \Illuminate\Contracts\Debug\ExceptionHandler $handler */
        $handler = $this->getApplication()
            ->make(ExceptionHandler::class);
        $output = new BufferedOutput();
        $exception = (new ValidationExceptionStub())->setErrors([
            'property' => ['Property must not be null'],
        ]);

        $handler->renderForConsole($output, $exception);
        $result = $output->fetch();

        self::assertStringContainsString('Validation Failures:', $result);
        self::assertStringContainsString('property - "Property must not be null"', $result);
    }

    public function testRenderForConsoleShowsValidationExceptionWithoutFailures(): void
    {
        /** @var \Illuminate\Contracts\Debug\ExceptionHandler $handler */
        $handler = $this->getApplication()
            ->make(ExceptionHandler::class);
        $output = new BufferedOutput();

        $handler->renderForConsole($output, new ValidationExceptionStub());
        $result = $output->fetch();

        self::assertStringContainsString('Validation Failures:', $result);
        self::assertStringContainsString('No validation errors in exception', $result);
    }

    #[DataProviderExternal(TestRenderWithDefaultBuilderDataProvider::class, 'provide')]
    public function testRenderWithDefaultBuilders(
        Request $request,
        Exception $exception,
        callable $assertResponse,
        ?array $translations = null,
    ): void {
        $app = $this->getApplication();
        /** @var \Illuminate\Contracts\Debug\ExceptionHandler $handler */
        $handler = $app->make(ExceptionHandler::class);

        /** @var \Illuminate\Translation\Translator $translator */
        $translator = $app->make('translator');
        $translator->addLines($this->prepareTranslations($translations), $translator->getLocale());

        $result = $handler->render($request, $exception);

        $assertResponse($result);
    }

    #[DataProviderExternal(TestRenderWithDefaultBuilderDataProvider::class, 'provideWithExtendedResponse')]
    public function testRenderWithDefaultBuildersAndExtendedResponse(
        Request $request,
        Exception $exception,
        callable $assertResponse,
        ?array $translations = null,
    ): void {
        $app = $this->getApplication([
            'easy-error-handler' => [
                'use_extended_response' => true,
            ],
        ]);
        /** @var \Illuminate\Contracts\Debug\ExceptionHandler $handler */
        $handler = $app->make(ExceptionHandler::class);

        /** @var \Illuminate\Translation\Translator $translator */
        $translator = $app->make('translator');
        $translator->addLines($this->prepareTranslations($translations), $translator->getLocale());

        $result = $handler->render($request, $exception);

        $assertResponse($result);
    }

    private function prepareTranslations(?array $translations = null): array
    {
        $result = [];
        foreach ($translations ?? [] as $key => $value) {
            $result[$key] = \str_replace('$', ':', (string)$value);
        }

        return $result;
    }
}
