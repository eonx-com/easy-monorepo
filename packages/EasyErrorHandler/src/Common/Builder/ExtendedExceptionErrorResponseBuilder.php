<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Builder;

use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerAwareInterface;
use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerAwareTrait;
use EonX\EasyErrorHandler\Common\Exception\TranslatableExceptionInterface;
use EonX\EasyErrorHandler\Common\Resolver\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Common\Translator\TranslatorInterface;
use Throwable;

final class ExtendedExceptionErrorResponseBuilder extends AbstractSingleKeyErrorResponseBuilder implements
    ErrorHandlerAwareInterface
{
    use ErrorHandlerAwareTrait;

    private const string EXCEPTION_KEY_CLASS = 'class';

    private const string EXCEPTION_KEY_FILE = 'file';

    private const string EXCEPTION_KEY_LINE = 'line';

    private const string EXCEPTION_KEY_MESSAGE = 'message';

    private const string EXCEPTION_KEY_TRACE = 'trace';

    /**
     * @param string[] $exceptionKeys
     */
    public function __construct(
        private readonly ErrorDetailsResolverInterface $errorDetailsResolver,
        private readonly TranslatorInterface $translator,
        private readonly array $exceptionKeys,
        string $key,
        ?int $priority = null,
    ) {
        parent::__construct($key, $priority);
    }

    protected function doBuildValue(Throwable $throwable, array $data): ?array
    {
        // Skip if not verbose
        if ($this->errorHandler->isVerbose() === false) {
            return null;
        }

        $details = $this->errorDetailsResolver->resolveSimpleDetails($throwable);

        return [
            $this->getExceptionKey(self::EXCEPTION_KEY_CLASS) => $details['class'],
            $this->getExceptionKey(self::EXCEPTION_KEY_FILE) => $details['file'],
            $this->getExceptionKey(self::EXCEPTION_KEY_LINE) => $details['line'],
            $this->getExceptionKey(self::EXCEPTION_KEY_MESSAGE) => $this->getMessage($throwable),
            $this->getExceptionKey(self::EXCEPTION_KEY_TRACE) => $details['trace'],
        ];
    }

    private function getExceptionKey(string $name): string
    {
        return $this->exceptionKeys[$name] ?? $name;
    }

    private function getMessage(Throwable $throwable): string
    {
        return $throwable instanceof TranslatableExceptionInterface
            ? $this->translator->trans($throwable->getMessage(), $throwable->getMessageParams())
            : $throwable->getMessage();
    }
}
