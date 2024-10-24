<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Builder;

use ApiPlatform\Validator\Exception\ConstraintViolationListAwareExceptionInterface;
use BackedEnum;
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

            if ($this->validationErrorCode !== null) {
                $data[$this->getKey('code')] = $this->validationErrorCode instanceof BackedEnum
                    ? $this->validationErrorCode->value
                    : $this->validationErrorCode;
            }
        }

        return parent::buildData($throwable, $data);
    }

    protected function buildViolations(Throwable $throwable): array
    {
        $violations = [];

        if ($throwable instanceof ConstraintViolationListAwareExceptionInterface) {
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
