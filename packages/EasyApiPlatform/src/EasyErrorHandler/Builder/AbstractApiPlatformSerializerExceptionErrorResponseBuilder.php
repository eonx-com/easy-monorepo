<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Builder;

use ApiPlatform\State\Provider\DeserializeProvider;
use ApiPlatform\Symfony\EventListener\DeserializeListener;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Throwable;

abstract class AbstractApiPlatformSerializerExceptionErrorResponseBuilder extends
    AbstractApiPlatformExceptionErrorResponseBuilder
{
    private const MESSAGE_PATTERN_CLASS = '/The type of the .* attribute for class "(.*)" must be.*/';

    final public function buildData(Throwable $throwable, array $data): array
    {
        $violations = $this->buildViolations($throwable);

        if (\count($violations) > 0) {
            $data[$this->getKey('message')] = $this->translator->trans('exceptions.not_valid', []);
            $data[$this->getKey('violations')] = $violations;
        }

        return parent::buildData($throwable, $data);
    }

    abstract protected function doBuildViolations(Throwable $throwable): array;

    final protected function buildViolations(Throwable $throwable): array
    {
        if ($this->isThrowableFromApiPlatformSerializer($throwable) === false) {
            return [];
        }

        return $this->doBuildViolations($throwable);
    }

    protected function buildViolationsForNotNormalizableValueException(NotNormalizableValueException $throwable): array
    {
        $path = $throwable->getPath();
        $matches = [];
        \preg_match(self::MESSAGE_PATTERN_CLASS, $throwable->getMessage(), $matches);

        if (isset($matches[1])) {
            $path = $this->nameConverter->normalize((string)$path, (string)$matches[1]);
        }

        return [
            $path => [
                match (true) {
                    \array_reduce(
                        [
                            '/The data is either not an string, an empty string, or null; you should pass a string' .
                            ' that can be parsed with the passed format or a valid DateTime string./',
                            '/Failed to parse time string \(.*\) at position .* \(.*\): .*/',
                        ],
                        static fn ($carry, $regex): bool => $carry || \preg_match($regex, $throwable->getMessage()),
                        false
                    ) => $this->translator->trans('violations.invalid_datetime', []),
                    default => $this->translator->trans(
                        'violations.invalid_type',
                        [
                            '%current_type%' => $throwable->getCurrentType(),
                            '%expected_types%' => \implode('|', $throwable->getExpectedTypes()),
                        ]
                    ),
                },
            ],
        ];
    }

    protected function isThrowableFromApiPlatformSerializer(Throwable $throwable): bool
    {
        foreach ($throwable->getTrace() as $trace) {
            if (
                (($trace['class'] ?? '') === DeserializeListener::class && $trace['function'] === 'onKernelRequest')
                || ((($trace['class'] ?? '') === DeserializeProvider::class && $trace['function'] === 'provide'))
            ) {
                return true;
            }
        }

        return false;
    }
}
