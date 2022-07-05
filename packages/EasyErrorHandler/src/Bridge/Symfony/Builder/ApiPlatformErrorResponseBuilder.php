<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Builder;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use EonX\EasyErrorHandler\Builders\AbstractErrorResponseBuilder;
use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Constraints\NotNull;
use Throwable;

final class ApiPlatformErrorResponseBuilder extends AbstractErrorResponseBuilder
{
    private const MESSAGE_PATTERN_INVALID_DATE = '/This value is not a valid date\/time\./';

    private const MESSAGE_PATTERN_INVALID_IRI = '/Invalid IRI "(.+)"/';

    private const MESSAGE_PATTERN_NESTED_DOCUMENTS_NOT_ALLOWED = '/Nested documents for attribute "(\w+)" ' .
    'are not allowed/';

    private const MESSAGE_PATTERN_NOT_IRI = '/Expected IRI or nested document for attribute "(\w+)", "(\w+)" given/';

    private const MESSAGE_PATTERN_NO_PARAMETER = '/Cannot create an instance of [\"]?([\w\\\\]+)[\"]? from serialized' .
    ' data because its constructor requires parameter "(\w+)" to be present/';

    private const MESSAGE_PATTERN_TYPE_ERROR = '/The type of the "(\w+)" attribute must be "(\w+)", "(\w+)" given/';

    /**
     * @var string[]
     */
    private array $keys;

    private TranslatorInterface $translator;

    /**
     * @param mixed[]|null $keys
     */
    public function __construct(TranslatorInterface $translator, ?array $keys = null, ?int $priority = null)
    {
        $this->translator = $translator;
        $this->keys = $keys ?? [];

        parent::__construct($priority);
    }

    public static function supports(Throwable $throwable): bool
    {
        $message = $throwable->getMessage();

        return match ($throwable::class) {
            InvalidArgumentException::class => \preg_match(self::MESSAGE_PATTERN_TYPE_ERROR, $message) === 1,
            MissingConstructorArgumentsException::class =>
                \preg_match(self::MESSAGE_PATTERN_NO_PARAMETER, $message) === 1,
            UnexpectedValueException::class =>
                \preg_match(self::MESSAGE_PATTERN_INVALID_DATE, $message) === 1 ||
                \preg_match(self::MESSAGE_PATTERN_INVALID_IRI, $message) === 1 ||
                \preg_match(self::MESSAGE_PATTERN_NESTED_DOCUMENTS_NOT_ALLOWED, $message) === 1 ||
                \preg_match(self::MESSAGE_PATTERN_NOT_IRI, $message) === 1,
            ValidationException::class => true,
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
        $data['message'] = 'Validation failed.';

        if ($throwable instanceof ValidationException) {
            $violations = [];

            foreach ($throwable->getConstraintViolationList() as $violation) {
                $propertyPath = $violation->getPropertyPath();

                if (isset($violations[$propertyPath]) === false) {
                    $violations[$propertyPath] = [];
                }

                $violations[$propertyPath][] = $violation->getMessage();
            }

            $exceptionKey = $this->getKey('exception');
            $exceptionMessageKey = $this->getKey('extended_exception_keys.message');

            if (\is_array($data[$exceptionKey] ?? null)) {
                $data[$exceptionKey][$exceptionMessageKey] = $this->translator->trans(
                    'exceptions.entity_not_valid',
                    []
                );
            }

            $data[$this->getKey('message')] = $this->translator->trans('exceptions.not_valid', []);
            $data[$this->getKey('violations')] = $violations;
        }

        if ($throwable instanceof InvalidArgumentException) {
            $matches = [];
            \preg_match(self::MESSAGE_PATTERN_TYPE_ERROR, $throwable->getMessage(), $matches);
            $data['violations'] = [
                $matches[1] => [
                    $matches[3] === 'NULL'
                        ? (new NotNull())->message
                        : \sprintf('The type of the value should be "%s", "%s" given.', $matches[2], $matches[3]),
                ],
            ];
        }

        if ($throwable instanceof MissingConstructorArgumentsException) {
            $matches = [];
            \preg_match(self::MESSAGE_PATTERN_NO_PARAMETER, $throwable->getMessage(), $matches);
            $data['violations'] = [
                $matches[2] => ['This value should be present.'],
            ];
        }

        if ($throwable instanceof UnexpectedValueException) {
            $message = $throwable->getMessage();
            $matches = [];

            switch (1) {
                case \preg_match(self::MESSAGE_PATTERN_INVALID_DATE, $message) === 1:
                case \preg_match(self::MESSAGE_PATTERN_INVALID_IRI, $message) === 1:
                    $data['violations'] = [$message];

                    break;
                case \preg_match(self::MESSAGE_PATTERN_NESTED_DOCUMENTS_NOT_ALLOWED, $message, $matches) === 1:
                    $data['violations'] = [
                        $matches[1] => ['This value should be an IRI.'],
                    ];

                    break;
                case \preg_match(self::MESSAGE_PATTERN_NOT_IRI, $message, $matches) === 1:
                    $data['violations'] = [
                        $matches[1] => [
                            $matches[2] === 'NULL'
                                ? (new NotNull())->message
                                : 'This value should be an IRI or a nested document.',
                        ],
                    ];

                    break;
            }
        }

        return parent::buildData($throwable, $data);
    }

    public function buildStatusCode(Throwable $throwable, ?int $statusCode = null): ?int
    {
        if ($throwable instanceof ValidationException) {
            $statusCode = 400;
        }

        return parent::buildStatusCode($throwable, $statusCode);
    }

    /**
     * @param mixed[]|null $keys
     */
    private function getKey(string $name, ?array $keys = null): string
    {
        $keys = $keys ?? $this->keys;
        $nameParts = \explode('.', $name);

        if (\count($nameParts) <= 1) {
            return $keys[$name] ?? $name;
        }

        $firstPartOfName = \array_shift($nameParts);

        if (isset($keys[$firstPartOfName]) === false) {
            return $name;
        }

        return $this->getKey(\implode('.', $nameParts), $keys[$firstPartOfName]);
    }
}
