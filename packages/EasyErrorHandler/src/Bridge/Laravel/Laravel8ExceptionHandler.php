<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Laravel;

use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\TranslatableExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\ValidationExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Debug\ExceptionHandler as IlluminateExceptionHandlerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class Laravel8ExceptionHandler implements IlluminateExceptionHandlerInterface
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
     */
    public function render($request, Throwable $throwable): Response
    {
        return $this->errorHandler->render($request, $throwable);
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function renderForConsole($output, Throwable $throwable): void
    {
        (new Application())->renderThrowable($throwable, $output);

        $this->renderTranslationToConsoleIfNeeded($output, $throwable);
        $this->renderValidationFailuresToConsoleIfNeeded($output, $throwable);
    }

    public function report(Throwable $throwable): void
    {
        $this->errorHandler->report($throwable);
    }

    public function shouldReport(Throwable $throwable): bool
    {
        // Delegate decision to error reporters
        return true;
    }

    /**
     * Returns a determined exception message.
     */
    private function determineExceptionMessage(Throwable $throwable): string
    {
        if ($throwable instanceof TranslatableExceptionInterface === false) {
            return $throwable->getMessage();
        }

        return $this->translator->trans($throwable->getMessage(), $throwable->getMessageParams());
    }

    /**
     * Renders a block with an exception message translation to the console if needed.
     */
    private function renderTranslationToConsoleIfNeeded(OutputInterface $output, Throwable $throwable): void
    {
        $exceptionMessage = $this->determineExceptionMessage($throwable);

        if ($exceptionMessage === $throwable->getMessage()) {
            return;
        }

        $message = \sprintf('Translated exception message: %s', $exceptionMessage);

        $style = new OutputStyle(new ArrayInput([]), $output);
        $style->block($message, null, 'fg=white;bg=red', ' ', true);
    }

    /**
     * Renders a block with an exception validation failures to the console if needed.
     */
    private function renderValidationFailuresToConsoleIfNeeded(OutputInterface $output, Throwable $throwable): void
    {
        if ($throwable instanceof ValidationExceptionInterface === false) {
            return;
        }

        $output->writeln('<error>Validation Failures:</error>');

        if (\count($throwable->getErrors()) === 0) {
            $output->writeln('No validation errors in exception');

            return;
        }

        foreach ($throwable->getErrors() as $key => $errors) {
            foreach ($errors as $error) {
                $output->writeln(\sprintf('<error>%s</error> - %s', $key, \json_encode($error)));
            }
        }
    }
}
