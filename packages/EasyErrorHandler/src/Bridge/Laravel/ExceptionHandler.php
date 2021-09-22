<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Laravel;

use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\TranslatableExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\ValidationExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use Exception;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Debug\ExceptionHandler as IlluminateExceptionHandlerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;

final class ExceptionHandler implements IlluminateExceptionHandlerInterface
{
    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface
     */
    private $errorHandler;

    /**
     * @var \EonX\EasyErrorHandler\Interfaces\TranslatorInterface
     */
    private $translator;

    public function __construct(ErrorHandlerInterface $errorHandler, TranslatorInterface $translator)
    {
        $this->errorHandler = $errorHandler;
        $this->translator = $translator;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Exception|\Throwable $exception
     */
    public function render($request, $exception): Response
    {
        return $this->errorHandler->render($request, $exception);
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Exception|\Throwable $exception
     */
    public function renderForConsole($output, $exception): void
    {
        (new Application())->renderThrowable($exception, $output);

        $this->renderTranslationToConsoleIfNeeded($output, $exception);
        $this->renderValidationFailuresToConsoleIfNeeded($output, $exception);
    }

    /**
     * @param \Exception|\Throwable $exception
     */
    public function report($exception): void
    {
        $this->errorHandler->report($exception);
    }

    /**
     * @param \Exception|\Throwable $exception
     */
    public function shouldReport($exception): bool
    {
        // Delegate decision to error reporters
        return true;
    }

    /**
     * Returns a determined exception message.
     *
     * @param \Exception|\Throwable $exception
     */
    private function determineExceptionMessage($exception): string
    {
        if ($exception instanceof TranslatableExceptionInterface === false) {
            return $exception->getMessage();
        }

        return $this->translator->trans($exception->getMessage(), $exception->getMessageParams());
    }

    /**
     * Renders a block with an exception message translation to the console if needed.
     *
     * @param \Exception|\Throwable $exception
     */
    private function renderTranslationToConsoleIfNeeded(OutputInterface $output, $exception): void
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
     *
     * @param \Exception|\Throwable $exception
     */
    private function renderValidationFailuresToConsoleIfNeeded(OutputInterface $output, $exception): void
    {
        if ($exception instanceof ValidationExceptionInterface === false) {
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
