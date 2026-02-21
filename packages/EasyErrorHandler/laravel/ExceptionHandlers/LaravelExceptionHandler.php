<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Laravel\ExceptionHandlers;

use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Common\Exception\TranslatableExceptionInterface;
use EonX\EasyErrorHandler\Common\Exception\WithErrorListExceptionInterface;
use EonX\EasyErrorHandler\Common\Translator\TranslatorInterface;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Debug\ExceptionHandler as IlluminateExceptionHandlerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final readonly class LaravelExceptionHandler implements IlluminateExceptionHandlerInterface
{
    public function __construct(
        private ErrorHandlerInterface $errorHandler,
        private TranslatorInterface $translator,
    ) {
    }

    public function render(mixed $request, Throwable $exception): Response
    {
        return $this->errorHandler->render($request, $exception);
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function renderForConsole(mixed $output, Throwable $exception): void
    {
        new Application()
            ->renderThrowable($exception, $output);

        $this->renderTranslationToConsoleIfNeeded($output, $exception);
        $this->renderValidationFailuresToConsoleIfNeeded($output, $exception);
    }

    public function report(Throwable $exception): void
    {
        $this->errorHandler->report($exception);
    }

    public function shouldReport(Throwable $exception): bool
    {
        // Delegate decision to error reporters
        return true;
    }

    /**
     * Returns a determined exception message.
     */
    private function determineExceptionMessage(Throwable $exception): string
    {
        if ($exception instanceof TranslatableExceptionInterface === false) {
            return $exception->getMessage();
        }

        return $this->translator->trans($exception->getMessage(), $exception->getMessageParams());
    }

    /**
     * Renders a block with an exception message translation to the console if needed.
     */
    private function renderTranslationToConsoleIfNeeded(OutputInterface $output, Throwable $exception): void
    {
        $exceptionMessage = $this->determineExceptionMessage($exception);

        if ($exceptionMessage === $exception->getMessage()) {
            return;
        }

        $message = \sprintf('Translated exception message: %s', $exceptionMessage);

        $style = new OutputStyle(new ArrayInput([]), $output);
        $style->block($message, null, 'fg=white;bg=red', ' ', true);
    }

    /**
     * Renders a block with an exception validation failures to the console if needed.
     */
    private function renderValidationFailuresToConsoleIfNeeded(OutputInterface $output, Throwable $exception): void
    {
        if ($exception instanceof WithErrorListExceptionInterface === false) {
            return;
        }

        $output->writeln('<error>Validation Failures:</error>');

        if (\count($exception->getErrors()) === 0) {
            $output->writeln('No validation errors in exception');

            return;
        }

        foreach ($exception->getErrors() as $key => $errors) {
            foreach ($errors as $error) {
                $output->writeln(\sprintf('<error>%s</error> - %s', $key, \json_encode($error)));
            }
        }
    }
}
