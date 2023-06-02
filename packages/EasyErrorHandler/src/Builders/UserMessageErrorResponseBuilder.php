<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Builders;

use EonX\EasyErrorHandler\Interfaces\Exceptions\TranslatableExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use Throwable;

final class UserMessageErrorResponseBuilder extends AbstractSingleKeyErrorResponseBuilder
{
    public const DEFAULT_KEY = 'message';

    public function __construct(
        private readonly TranslatorInterface $translator,
        ?string $key = null,
        ?int $priority = null,
    ) {
        parent::__construct($key, $priority);
    }

    /**
     * @param mixed[] $data
     */
    protected function doBuildValue(Throwable $throwable, array $data): string
    {
        $message = null;
        $parameters = [];

        if ($throwable instanceof TranslatableExceptionInterface) {
            $message = $throwable->getUserMessage();
            $parameters = $throwable->getUserMessageParams();
        }

        return $this->translator->trans(
            $message ?? TranslatableExceptionInterface::DEFAULT_USER_MESSAGE,
            $parameters
        );
    }

    protected function getDefaultKey(): string
    {
        return self::DEFAULT_KEY;
    }
}
