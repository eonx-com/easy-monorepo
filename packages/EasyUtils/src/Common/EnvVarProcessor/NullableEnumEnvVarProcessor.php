<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\EnvVarProcessor;

use BackedEnum;
use Closure;
use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

final class NullableEnumEnvVarProcessor implements EnvVarProcessorInterface
{
    /**
     * @return array<string, string>
     */
    public static function getProvidedTypes(): array
    {
        return ['nullable_enum' => BackedEnum::class];
    }

    public function getEnv(string $prefix, string $name, Closure $getEnv): ?BackedEnum
    {
        if ($prefix !== 'nullable_enum') {
            throw new RuntimeException(\sprintf('Unsupported env var prefix "%s" for env name "%s".', $prefix, $name));
        }

        $colonPosition = \strpos($name, ':');

        if ($colonPosition === false) {
            throw new RuntimeException(
                \sprintf('Invalid env "enum:%s": a "%s" class-string should be provided.', $name, BackedEnum::class)
            );
        }

        /** @var class-string $backedEnumClassName */
        $backedEnumClassName = \substr($name, 0, $colonPosition);

        if (\is_subclass_of($backedEnumClassName, BackedEnum::class) === false) {
            throw new RuntimeException(\sprintf('"%s" is not a "%s".', $backedEnumClassName, BackedEnum::class));
        }

        $nextName = \substr($name, $colonPosition + 1);
        $backedEnumValue = $getEnv($nextName);

        if (\is_string($backedEnumValue) === false && \is_int($backedEnumValue) === false) {
            throw new RuntimeException(
                \sprintf('Resolved value of "%s" did not result in a string or int value.', $nextName)
            );
        }

        return $backedEnumClassName::tryFrom($backedEnumValue);
    }
}
