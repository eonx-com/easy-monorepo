<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Builder;

use ApiPlatform\Symfony\Validator\Exception\ValidationException;
use Throwable;

final class ApiPlatformValidationExceptionErrorResponseBuilder extends AbstractApiPlatformExceptionErrorResponseBuilder
{
    public function buildData(Throwable $throwable, array $data): array
    {
        $violations = $this->buildViolations($throwable);

        if (\count($violations) > 0) {
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

    protected function buildViolations(Throwable $throwable): array
    {
        $violations = [];

        if ($throwable instanceof ValidationException) {
            foreach ($throwable->getConstraintViolationList() as $violation) {
                $propertyPath = $violation->getPropertyPath();

                if (isset($violations[$propertyPath]) === false) {
                    $violations[$propertyPath] = [];
                }

                $violations[$propertyPath][] = $violation->getMessage();
            }
        }

        return $violations;
    }
}
