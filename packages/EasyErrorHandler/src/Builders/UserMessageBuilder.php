<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Builders;

use EonX\EasyErrorHandler\Interfaces\Exceptions\TranslatableExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use Throwable;

final class UserMessageBuilder extends AbstractSingleKeyErrorResponseBuilder
{
    /**
     * @var string
     */
    private const DEFAULT_USER_MESSAGE = 'easy-error-handler::messages.default_user_message';

    /**
     * @var \EonX\EasyErrorHandler\Interfaces\TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator, ?string $key = null, ?int $priority = null)
    {
        $this->translator = $translator;
        
        parent::__construct($key, $priority);
    }

    protected function doBuildValue(Throwable $throwable, array $data)
    {
        $message = null;
        $parameters = [];

        if ($throwable instanceof TranslatableExceptionInterface) {
            $message = $throwable->getUserMessage();
            $parameters = $throwable->getUserMessageParams();
        }

        return $this->translator->trans($message ?? self::DEFAULT_USER_MESSAGE, $parameters);
    }

    protected function getDefaultKey(): string
    {
        return 'message';
    }
}
