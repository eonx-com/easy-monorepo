<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Builder;

use EonX\EasyErrorHandler\Common\Exception\TranslatableExceptionInterface;
use EonX\EasyErrorHandler\Common\Translator\TranslatorInterface;
use Throwable;

final class UserMessageErrorResponseBuilder extends AbstractSingleKeyErrorResponseBuilder
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        string $key,
        ?int $priority = null,
    ) {
        parent::__construct($key, $priority);
    }

    protected function doBuildValue(Throwable $throwable, array $data): string
    {
        $message = 'exceptions.default_user_message';
        $parameters = [];

        if ($throwable instanceof TranslatableExceptionInterface) {
            $message = $throwable->getUserMessage();
            $parameters = $throwable->getUserMessageParams();
        }

        if ($throwable->isTranslatable() === false) {
            return $message;
        }

        return $this->translator->trans($message, $parameters);
    }
}
