<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Builder;

use ApiPlatform\Core\Exception\InvalidArgumentException as LegacyInvalidArgumentException;
use ApiPlatform\Exception\InvalidArgumentException;
use EonX\EasyErrorHandler\Builders\AbstractErrorResponseBuilder;
use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Constraints\NotNull;
use Throwable;

final class ApiPlatformValidationErrorResponseBuilder extends AbstractErrorResponseBuilder
{
    private const KEY_MESSAGE = 'message';

    private const KEY_VIOLATIONS = 'violations';

    private const MESSAGE_NOT_VALID = 'exceptions.not_valid';

    private const MESSAGE_PATTERN_ATTRIBUTE_TYPE_ERROR = '/The type of the "(\w+)" attribute for class "(.*)" must be' .
    ' one of "(\w+)" \("(\w+)" given\)\./';

    private const MESSAGE_PATTERN_INPUT_DATA_MISFORMATTED = '/The input data is misformatted\./';

    private const MESSAGE_PATTERN_INVALID_DATE = '/This value is not a valid date\/time\./';

    private const MESSAGE_PATTERN_INVALID_IRI = '/Invalid IRI "(.+)"/';

    private const MESSAGE_PATTERN_NESTED_DOCUMENTS_NOT_ALLOWED = '/Nested documents for attribute "(\w+)" ' .
    'are not allowed/';

    private const MESSAGE_PATTERN_NOT_IRI = '/Expected IRI or nested document for attribute "(\w+)", "(\w+)" given/';

    private const MESSAGE_PATTERN_NO_PARAMETER_API_PLATFORM = '/Cannot create an instance of [\"]?([\w\\\\]+)[\"]?' .
    ' from serialized data because its constructor requires parameter "(\w+)" to be present/';

    private const MESSAGE_PATTERN_NO_PARAMETER_SYMFONY = '/Cannot create an instance of [\"]?([\w\\\\]+)[\"]?' .
    ' from serialized data because its constructor requires the following parameters to be present : "(.*)"/';

    private const MESSAGE_PATTERN_TYPE_ERROR = '/The type of the "(\w+)" attribute must be "(\w+)", "(\w+)" given/';

    private const VALUE_NULL = 'NULL';

    private const VIOLATION_PATTERN_TYPE_ERROR = 'The type of the value should be "%s", "%s" given.';

    private const VIOLATION_VALUE_SHOULD_BE_IRI = 'This value should be an IRI.';

    private const VIOLATION_VALUE_SHOULD_BE_IRI_OR_NESTED_DOCUMENT = 'This value should be an IRI ' .
    'or a nested document.';

    private const VIOLATION_VALUE_SHOULD_BE_PRESENT = 'This value should be present.';

    /**
     * @var mixed[]
     */
    private readonly array $keys;

    /**
     * @param null|mixed[] $keys
     */
    public function __construct(
        private readonly TranslatorInterface $translator,
        ?array $keys = null,
        ?int $priority = null,
    ) {
        $this->keys = $keys ?? [];

        parent::__construct($priority);
    }

    public static function supports(Throwable $throwable): bool
    {
        $message = $throwable->getMessage();

        // TODO: refactor in 5.0. Use the ApiPlatform\Symfony\Bundle\ApiPlatformBundle class only.
        $invalidArgumentExceptionClass = null;
        if (\class_exists(InvalidArgumentException::class)) {
            $invalidArgumentExceptionClass = InvalidArgumentException::class;
        }

        return match ($throwable::class) {
            $invalidArgumentExceptionClass, LegacyInvalidArgumentException::class =>
                \preg_match(self::MESSAGE_PATTERN_TYPE_ERROR, $message) === 1,
            MissingConstructorArgumentsException::class =>
                \preg_match(self::MESSAGE_PATTERN_NO_PARAMETER_API_PLATFORM, $message) === 1 ||
                \preg_match(self::MESSAGE_PATTERN_NO_PARAMETER_SYMFONY, $message) === 1,
            UnexpectedValueException::class =>
                \preg_match(self::MESSAGE_PATTERN_TYPE_ERROR, $message) === 1 ||
                \preg_match(self::MESSAGE_PATTERN_INVALID_DATE, $message) === 1 ||
                \preg_match(self::MESSAGE_PATTERN_INVALID_IRI, $message) === 1 ||
                \preg_match(self::MESSAGE_PATTERN_NESTED_DOCUMENTS_NOT_ALLOWED, $message) === 1 ||
                \preg_match(self::MESSAGE_PATTERN_NOT_IRI, $message) === 1 ||
                \preg_match(self::MESSAGE_PATTERN_INPUT_DATA_MISFORMATTED, $message) === 1,
            default => false
        };
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity) We have many exceptions
     */
    public function buildData(Throwable $throwable, array $data): array
    {
        if (self::supports($throwable) === false) {
            return parent::buildData($throwable, $data);
        }

        $messageKey = $this->getKey(self::KEY_MESSAGE);
        $violationsKey = $this->getKey(self::KEY_VIOLATIONS);

        $data[$messageKey] = $this->translator->trans(self::MESSAGE_NOT_VALID, []);

        $isInvalidArgumentException = null;

        // TODO: refactor in 5.0. Use the ApiPlatform\Symfony\Bundle\ApiPlatformBundle class only.
        if (\class_exists(InvalidArgumentException::class)) {
            $isInvalidArgumentException = $throwable instanceof InvalidArgumentException;
        }

        if (\class_exists(InvalidArgumentException::class) === false) {
            $isInvalidArgumentException = $throwable instanceof LegacyInvalidArgumentException;
        }

        if ($isInvalidArgumentException) {
            $matches = [];
            \preg_match(self::MESSAGE_PATTERN_TYPE_ERROR, $throwable->getMessage(), $matches);
            $data[$violationsKey] = [
                $matches[1] => [
                    $matches[3] === self::VALUE_NULL
                        ? (new NotNull())->message
                        : \sprintf(self::VIOLATION_PATTERN_TYPE_ERROR, $matches[2], $matches[3]),
                ],
            ];
        }

        if ($throwable instanceof MissingConstructorArgumentsException) {
            $matches = [];
            \preg_match(self::MESSAGE_PATTERN_NO_PARAMETER_API_PLATFORM, $throwable->getMessage(), $matches);
            if ($matches !== []) {
                $data[$violationsKey][$matches[2]] = [self::VIOLATION_VALUE_SHOULD_BE_PRESENT];
            }
        }

        if ($throwable instanceof MissingConstructorArgumentsException) {
            $matches = [];
            \preg_match(self::MESSAGE_PATTERN_NO_PARAMETER_SYMFONY, $throwable->getMessage(), $matches);
            $matches = \explode('", "', $matches[2] ?? '');
            foreach ($matches as $match) {
                $match = \str_replace('$', '', $match);
                $data[$violationsKey][$match] = [self::VIOLATION_VALUE_SHOULD_BE_PRESENT];
            }
        }

        if ($throwable instanceof UnexpectedValueException) {
            $message = $throwable->getMessage();
            $matches = [];

            switch (1) {
                case \preg_match(self::MESSAGE_PATTERN_TYPE_ERROR, $throwable->getMessage(), $matches) === 1:
                    $data[$violationsKey] = [
                        $matches[1] => [
                            $matches[3] === self::VALUE_NULL
                                ? (new NotNull())->message
                                : \sprintf(self::VIOLATION_PATTERN_TYPE_ERROR, $matches[2], $matches[3]),
                        ],
                    ];

                    break;
                case \preg_match(self::MESSAGE_PATTERN_INPUT_DATA_MISFORMATTED, $throwable->getMessage()) === 1:
                    $hasAttributeTypeError = (bool)\preg_match(
                        self::MESSAGE_PATTERN_ATTRIBUTE_TYPE_ERROR,
                        (string)$throwable->getPrevious()?->getMessage(),
                        $matches
                    );

                    if ($hasAttributeTypeError === true) {
                        $data[$violationsKey] = [
                            $matches[1] => [
                                $matches[4] === self::VALUE_NULL
                                    ? (new NotNull())->message
                                    : \sprintf(self::VIOLATION_PATTERN_TYPE_ERROR, $matches[3], $matches[4]),
                            ],
                        ];
                    }

                    if ($hasAttributeTypeError === false) {
                        $data[$violationsKey] = [$message];
                    }

                    break;
                case \preg_match(self::MESSAGE_PATTERN_INVALID_DATE, $message) === 1:
                case \preg_match(self::MESSAGE_PATTERN_INVALID_IRI, $message) === 1:
                    $data[$violationsKey] = [$message];

                    break;
                case \preg_match(self::MESSAGE_PATTERN_NESTED_DOCUMENTS_NOT_ALLOWED, $message, $matches) === 1:
                    $data[$violationsKey] = [
                        $matches[1] => [self::VIOLATION_VALUE_SHOULD_BE_IRI],
                    ];

                    break;
                case \preg_match(self::MESSAGE_PATTERN_NOT_IRI, $message, $matches) === 1:
                    $data[$violationsKey] = [
                        $matches[1] => [
                            $matches[2] === self::VALUE_NULL
                                ? (new NotNull())->message
                                : self::VIOLATION_VALUE_SHOULD_BE_IRI_OR_NESTED_DOCUMENT,
                        ],
                    ];

                    break;
            }
        }

        return parent::buildData($throwable, $data);
    }

    public function buildStatusCode(Throwable $throwable, ?int $statusCode = null): ?int
    {
        if (self::supports($throwable) === false) {
            return parent::buildStatusCode($throwable, $statusCode);
        }

        return Response::HTTP_BAD_REQUEST;
    }

    private function getKey(string $name): string
    {
        return $this->keys[$name] ?? $name;
    }
}
