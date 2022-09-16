<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Builder;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException as LegacyValidationException;
use ApiPlatform\Symfony\Validator\Exception\ValidationException;
use EonX\EasyErrorHandler\Builders\AbstractErrorResponseBuilder;
use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use Throwable;

final class ApiPlatformValidationExceptionResponseBuilder extends AbstractErrorResponseBuilder
{
    /**
     * @var string[]
     */
    private $keys;

    /**
     * @var \EonX\EasyErrorHandler\Interfaces\TranslatorInterface
     */
    private $translator;

    /**
     * @param null|mixed[] $keys
     */
    public function __construct(TranslatorInterface $translator, ?array $keys = null, ?int $priority = null)
    {
        $this->translator = $translator;
        $this->keys = $keys ?? [];

        parent::__construct($priority);
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public function buildData(Throwable $throwable, array $data): array
    {
        // TODO: refactor in 5.0. Use the ApiPlatform\Symfony\Bundle\ApiPlatformBundle class only.
        if (\class_exists(ValidationException::class)) {
            $isValidationException = $throwable instanceof ValidationException
                || $throwable instanceof LegacyValidationException;
        }

        if (\class_exists(ValidationException::class) === false) {
            $isValidationException = $throwable instanceof LegacyValidationException;
        }

        if ($isValidationException) {
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

        return parent::buildData($throwable, $data);
    }

    public function buildStatusCode(Throwable $throwable, ?int $statusCode = null): ?int
    {
        // TODO: refactor in 5.0. Use the ApiPlatform\Symfony\Bundle\ApiPlatformBundle class only.
        if (\class_exists(ValidationException::class)) {
            $isValidationException = $throwable instanceof ValidationException
                || $throwable instanceof LegacyValidationException;
        }

        if (\class_exists(ValidationException::class) === false) {
            $isValidationException = $throwable instanceof LegacyValidationException;
        }

        if ($isValidationException) {
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
