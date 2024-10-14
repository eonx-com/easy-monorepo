<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Builder;

use EonX\EasyErrorHandler\Common\Translator\TranslatorInterface;
use Throwable;

final class MessageErrorResponseBuilder extends AbstractSingleKeyErrorResponseBuilder
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        string $key,
        ?int $priority = null,
        private ?array $exceptionMessages = null,
    ) {
        $this->exceptionMessages = $this->exceptionMessages ?? [];

        parent::__construct($key, $priority);
    }

    protected function doBuildValue(Throwable $throwable, array $data): string
    {
        if (isset($this->exceptionMessages[$throwable::class])) {
            return $this->translator->trans($this->exceptionMessages[$throwable::class], []);
        }

        return $data[$this->key] ?? '';
    }
}
