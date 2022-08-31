<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Builders;

use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerAwareInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\TranslatableExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use EonX\EasyErrorHandler\Traits\ErrorHandlerAwareTrait;
use Throwable;

final class ExtendedExceptionErrorResponseBuilder extends AbstractSingleKeyErrorResponseBuilder implements
    ErrorHandlerAwareInterface
{
    use ErrorHandlerAwareTrait;

    public const DEFAULT_KEY = 'exception';

    private const EXCEPTION_KEY_CLASS = 'class';

    private const EXCEPTION_KEY_FILE = 'file';

    private const EXCEPTION_KEY_LINE = 'line';

    private const EXCEPTION_KEY_MESSAGE = 'message';

    private const EXCEPTION_KEY_TRACE = 'trace';

    /**
     * @param string[] $exceptionKeys
     */
    public function __construct(
        private readonly ErrorDetailsResolverInterface $errorDetailsResolver,
        private readonly TranslatorInterface $translator,
        private readonly array $exceptionKeys = [],
        ?string $key = null,
        ?int $priority = null
    ) {
        parent::__construct($key, $priority);
    }

    /**
     * @return mixed[]|null
     */
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

    protected function getDefaultKey(): string
    {
        return self::DEFAULT_KEY;
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
