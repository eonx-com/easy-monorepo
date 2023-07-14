<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces\Exceptions;

interface TranslatableExceptionInterface
{
    public const USER_MESSAGE_BAD_REQUEST = 'exceptions.bad_request';

    public const USER_MESSAGE_CONFLICT = 'exceptions.conflict';

    public const USER_MESSAGE_DEFAULT = 'exceptions.default_user_message';

    public const USER_MESSAGE_FORBIDDEN = 'exceptions.forbidden';

    public const USER_MESSAGE_NOT_FOUND = 'exceptions.not_found';

    public const USER_MESSAGE_NOT_VALID = 'exceptions.not_valid';

    public const USER_MESSAGE_UNAUTHORIZED = 'exceptions.unauthorized';

    /**
     * Returns the translation domain.
     */
    public function getDomain(): ?string;

    /**
     * Returns the exception message parameters.
     *
     * @return mixed[]
     */
    public function getMessageParams(): array;

    /**
     * Returns the user-friendly message.
     */
    public function getUserMessage(): string;

    /**
     * Returns the user-friendly message parameters.
     *
     * @return mixed[]
     */
    public function getUserMessageParams(): array;
}
