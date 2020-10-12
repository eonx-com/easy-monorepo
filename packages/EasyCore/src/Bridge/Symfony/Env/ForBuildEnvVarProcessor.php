<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Env;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;
use Symfony\Component\DependencyInjection\Exception\EnvNotFoundException;

final class ForBuildEnvVarProcessor implements EnvVarProcessorInterface
{
    /**
     * @return string[] The PHP-types managed by getEnv(), keyed by prefixes
     */
    public static function getProvidedTypes(): array
    {
        return [
            'for_build' => 'string',
        ];
    }

    /**
     * Returns the value of the given variable as managed by the current instance.
     *
     * @param string $prefix The namespace of the variable
     * @param string $name The name of the variable within the namespace
     * @param \Closure $getEnv A closure that allows fetching more env vars
     *
     * @return mixed
     */
    public function getEnv($prefix, $name, \Closure $getEnv)
    {
        try {
            return $getEnv($name);
        } catch (EnvNotFoundException $exception) {
            // Default to empty string if env var required for build
            return '';
        }
    }
}
