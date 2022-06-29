<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Env;

use Closure;
use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;
use Symfony\Component\DependencyInjection\Exception\EnvNotFoundException;

final class ForBuildEnvVarProcessor implements EnvVarProcessorInterface
{
    /**
     * @return array<string, string> The PHP-types managed by getEnv(), keyed by prefixes
     */
    public static function getProvidedTypes(): array
    {
        return [
            'for_build' => 'string',
        ];
    }

    public function getEnv(string $prefix, string $name, Closure $getEnv): mixed
    {
        try {
            return $getEnv($name);
        } catch (EnvNotFoundException $exception) {
            // Default to empty string if env var required for build
            return '';
        }
    }
}
