<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\EnvVarProcessor;

use Closure;
use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;
use Symfony\Component\DependencyInjection\Exception\EnvNotFoundException;

final class ForBuildEnvVarProcessor implements EnvVarProcessorInterface
{
    /**
     * @return array<string, string>
     */
    public static function getProvidedTypes(): array
    {
        return ['for_build' => 'string'];
    }

    public function getEnv(string $prefix, string $name, Closure $getEnv): mixed
    {
        try {
            return $getEnv($name);
        } catch (EnvNotFoundException) {
            // Default to empty string if the env var is required for build
            return '';
        }
    }
}
