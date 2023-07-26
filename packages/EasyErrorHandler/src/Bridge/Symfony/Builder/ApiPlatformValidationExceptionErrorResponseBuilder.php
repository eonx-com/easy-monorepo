<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Builder;

use ApiPlatform\Symfony\Validator\Exception\ValidationException;
use EonX\EasyErrorHandler\Builders\AbstractErrorResponseBuilder;
use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ApiPlatformValidationExceptionErrorResponseBuilder extends AbstractErrorResponseBuilder
{
    private const KEY_EXCEPTION = 'exception';

    private const KEY_EXCEPTION_MESSAGE = 'extended_exception_keys.message';

    private const KEY_MESSAGE = 'message';

    private const KEY_NAME_SEPARATOR = '.';

    private const KEY_VIOLATIONS = 'violations';

    private const MESSAGE_ENTITY_NOT_VALID = 'exceptions.entity_not_valid';

    private const MESSAGE_NOT_VALID = 'exceptions.not_valid';

    /**
     * @var mixed[]
     */
    private readonly array $keys;

    /**
     * @param mixed[]|null $keys
     */
    public function __construct(
        private readonly TranslatorInterface $translator,
        ?array $keys = null,
        ?int $priority = null,
    ) {
        $this->keys = $keys ?? [];

        parent::__construct($priority);
    }

    /**
     * @param \ApiPlatform\Symfony\Validator\Exception\ValidationException $throwable
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public function buildData(Throwable $throwable, array $data): array
    {
        if ($throwable instanceof ValidationException) {
            $violations = [];

            foreach ($throwable->getConstraintViolationList() as $violation) {
                $propertyPath = $violation->getPropertyPath();

                if (isset($violations[$propertyPath]) === false) {
                    $violations[$propertyPath] = [];
                }

                $violations[$propertyPath][] = $violation->getMessage();
            }

            $exceptionKey = $this->getKey(self::KEY_EXCEPTION);
            $exceptionMessageKey = $this->getKey(self::KEY_EXCEPTION_MESSAGE);

            if (\is_array($data[$exceptionKey] ?? null)) {
                $data[$exceptionKey][$exceptionMessageKey] = $this->translator->trans(
                    self::MESSAGE_ENTITY_NOT_VALID,
                    []
                );
            }

            $data[$this->getKey(self::KEY_MESSAGE)] = $this->translator->trans(self::MESSAGE_NOT_VALID, []);
            $data[$this->getKey(self::KEY_VIOLATIONS)] = $violations;
        }

        return parent::buildData($throwable, $data);
    }

    public function buildStatusCode(Throwable $throwable, ?int $statusCode = null): ?int
    {
        if ($throwable instanceof ValidationException) {
            $statusCode = Response::HTTP_BAD_REQUEST;
        }

        return parent::buildStatusCode($throwable, $statusCode);
    }

    /**
     * @param mixed[]|null $keys
     */
    private function getKey(string $name, ?array $keys = null): string
    {
        $keys ??= $this->keys;
        $nameParts = \explode(self::KEY_NAME_SEPARATOR, $name);

        if (\count($nameParts) <= 1) {
            return $keys[$name] ?? $name;
        }

        $firstPartOfName = \array_shift($nameParts);

        if (isset($keys[$firstPartOfName]) === false) {
            return $name;
        }

        return $this->getKey(\implode(self::KEY_NAME_SEPARATOR, $nameParts), $keys[$firstPartOfName]);
    }
}
