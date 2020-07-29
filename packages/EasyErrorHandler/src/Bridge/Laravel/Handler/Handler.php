<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Laravel\Handler;

use Carbon\Carbon;
use EoneoPay\ApiFormats\Bridge\Laravel\Traits\LaravelResponseTrait;
use EoneoPay\ApiFormats\External\Interfaces\Psr7\Psr7FactoryInterface;
use EoneoPay\ApiFormats\Interfaces\EncoderGuesserInterface;
use EoneoPay\ApiFormats\Interfaces\EncoderInterface;
use EonX\EasyErrorHandler\Interfaces\LogLevelAwareExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\StatusCodeAwareExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\SubCodeAwareExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\TranslatableExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\ValidationExceptionInterface;
use EonX\EasyLogging\Interfaces\LoggerInterface;
use Exception;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr as IlluminateArr;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

class Handler implements ExceptionHandler
{
    use LaravelResponseTrait;

    /**
     * @var string
     */
    protected const DEFAULT_USER_MESSAGE = 'easy-error-handler::messages.default_user_message';

    /**
     * @var string[]
     */
    protected $dontReport = [];

    /**
     * @var \EoneoPay\ApiFormats\Interfaces\EncoderGuesserInterface
     */
    protected $encoderGuesser;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    /**
     * @var \EonX\EasyLogging\Interfaces\LoggerInterface
     */
    private $logger;

    /**
     * @var \EoneoPay\ApiFormats\External\Interfaces\Psr7\Psr7FactoryInterface
     */
    private $psr7Factory;

    /**
     * @var \Illuminate\Contracts\Translation\Translator
     */
    private $translator;

    public function __construct(
        EncoderGuesserInterface $encoderGuesser,
        Psr7FactoryInterface $psr7Factory,
        Repository $config,
        Translator $translator,
        LoggerInterface $logger
    ) {
        $this->encoderGuesser = $encoderGuesser;
        $this->psr7Factory = $psr7Factory;
        $this->config = $config;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function render($request, Exception $exception): Response
    {
        $encoder = $this->getEncoderFromRequest($request);

        $responseData = IlluminateArr::sortRecursive($this->getResponseData($exception));
        $statusCode = $this->getResponseStatusCode($exception);
        $headers = null;

        $encodedPsrResponse = $encoder->encode($responseData, $statusCode, $headers);

        return $this->createLaravelResponseFromPsr($encodedPsrResponse);
    }

    /**
     * {@inheritdoc}
     */
    public function renderForConsole($output, Exception $exception): void
    {
        (new ConsoleApplication())->renderThrowable($exception, $output);

        $this->renderTranslationToConsoleIfNeeded($output, $exception);

        $this->renderValidationFailuresToConsoleIfNeeded($output, $exception);
    }

    /**
     * {@inheritdoc}
     */
    public function report(Exception $exception): void
    {
        if ($this->shouldReport($exception) === false) {
            return;
        }

        $logLevel = $exception instanceof LogLevelAwareExceptionInterface ?
            $exception->getLogLevel() :
            LoggerInterface::LEVEL_ERROR;

        $this->logger->exception($exception, $logLevel);
    }

    /**
     * {@inheritdoc}
     */
    public function shouldReport(Exception $exception): bool
    {
        foreach ($this->dontReport as $type) {
            if ($exception instanceof $type) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns a determined user message.
     */
    protected function determineUserMessage(Exception $exception): string
    {
        $userMessage = null;
        $userMessageParams = [];
        if ($exception instanceof TranslatableExceptionInterface) {
            $userMessage = $exception->getUserMessage();
            $userMessageParams = $exception->getUserMessageParams();
        }

        return $this->translateMessage($userMessage ?? self::DEFAULT_USER_MESSAGE, $userMessageParams);
    }

    /**
     * Returns the current datetime string.
     */
    protected function getCurrentDateTimeString(): string
    {
        return Carbon::now()->toIso8601ZuluString();
    }

    /**
     * Returns an exception converted into an array.
     *
     * @return mixed[]
     */
    protected function getResponseData(Exception $exception): array
    {
        return $this->isExtendedResponse() ?
            $this->getExtendedResponseData($exception) :
            $this->getShortResponseData($exception);
    }

    /**
     * Returns a response status code based on an exception.
     */
    protected function getResponseStatusCode(Exception $exception): int
    {
        if ($exception instanceof StatusCodeAwareExceptionInterface === false) {
            return 500;
        }

        return $exception->getStatusCode();
    }

    /**
     * Checks whether the extended response should be used.
     */
    protected function isExtendedResponse(): bool
    {
        return $this->config->get('easy-error-handler.use_extended_response', false) === true;
    }

    /**
     * Returns a determined exception message.
     */
    private function determineExceptionMessage(Exception $exception): string
    {
        if ($exception instanceof TranslatableExceptionInterface === false) {
            return $exception->getMessage();
        }

        return $this->translateMessage($exception->getMessage(), $exception->getMessageParams());
    }

    /**
     * Returns the ApiFormats encoder for the given request.
     */
    private function getEncoderFromRequest(Request $request): EncoderInterface
    {
        return $request->get('_encoder', $this->encoderGuesser->defaultEncoder());
    }

    /**
     * Returns an extended response data.
     *
     * @return mixed[]
     */
    private function getExtendedResponseData(Exception $exception): array
    {
        $responseFields = $this->config->get('easy-error-handler.response');

        $exceptionKey = $responseFields['exception'] ?? 'exception';
        $exceptionClassKey = $responseFields['exception']['class'] ?? 'class';
        $exceptionFileKey = $responseFields['exception']['file'] ?? 'file';
        $exceptionLineKey = $responseFields['exception']['line'] ?? 'line';
        $exceptionMessageKey = $responseFields['exception']['message'] ?? 'message';
        $exceptionTraceKey = $responseFields['exception']['trace'] ?? 'trace';

        return $this->getShortResponseData($exception) +
            [
                $exceptionKey => [
                    $exceptionClassKey => \get_class($exception),
                    $exceptionFileKey => $exception->getFile(),
                    $exceptionLineKey => $exception->getLine(),
                    $exceptionMessageKey => $this->determineExceptionMessage($exception),
                    $exceptionTraceKey => \collect($exception->getTrace())->map(
                        static function ($trace) {
                            return IlluminateArr::except($trace, ['args']);
                        }
                    )->all(),
                ],
            ];
    }

    /**
     * Returns a short response data.
     *
     * @return mixed[]
     */
    private function getShortResponseData(Exception $exception): array
    {
        $responseFields = $this->config->get('easy-error-handler.response');

        $codeKey = $responseFields['code'] ?? 'code';
        $messageKey = $responseFields['message'] ?? 'message';
        $timeKey = $responseFields['time'] ?? 'time';

        $responseData = [
            $codeKey => $exception->getCode(),
            $messageKey => $this->determineUserMessage($exception),
            $timeKey => $this->getCurrentDateTimeString(),
        ];

        if ($exception instanceof SubCodeAwareExceptionInterface) {
            $subCodeKey = $responseFields['sub_code'] ?? 'sub_code';
            $responseData[$subCodeKey] = $exception->getSubCode();
        }

        if ($exception instanceof ValidationExceptionInterface) {
            $violationsKey = $responseFields['violations'] ?? 'violations';
            $responseData[$violationsKey] = $exception->getErrors();
        }

        return $responseData;
    }

    /**
     * Renders a block with an exception message translation to the console if needed.
     */
    private function renderTranslationToConsoleIfNeeded(OutputInterface $output, Exception $exception): void
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
    private function renderValidationFailuresToConsoleIfNeeded(OutputInterface $output, Exception $exception): void
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

    /**
     * Tries to translate a message if it is a key from a lang file and returns it.
     *
     * @param mixed[] $parameters
     */
    private function translateMessage(string $message, array $parameters): string
    {
        // TODO: rework after upgrading all the illuminate and laravel packages to ^6.0
        $method = \method_exists($this->translator, 'trans') ? 'trans' : 'get';

        return $this->translator->{$method}(\trim($message), $parameters);
    }
}
