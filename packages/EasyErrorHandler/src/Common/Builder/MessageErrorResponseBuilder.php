<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Builder;

use EonX\EasyErrorHandler\Common\Translator\TranslatorInterface;
use Throwable;

final class MessageErrorResponseBuilder extends AbstractSingleKeyErrorResponseBuilder
{
    private readonly array $exceptionToMessage;

    public function __construct(
        private readonly TranslatorInterface $translator,
        string $key,
        ?int $priority = null,
        ?array $exceptionToMessage = null,
    ) {
        $this->exceptionToMessage = $exceptionToMessage ?? [];

        parent::__construct($key, $priority);
    }

    protected function doBuildValue(Throwable $throwable, array $data): string
    {
        foreach ($this->exceptionToMessage as $class => $message) {
            if (\is_a($throwable, $class)) {
                return $this->translator->trans($message, []);
            }
        }

        return $data[$this->key] ?? '';
    }
}
