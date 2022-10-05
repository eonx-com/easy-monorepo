<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Helpers;

use EonX\EasyUtils\Helpers\EnvVarSubstitutionHelper;

final class EnvVarHelper
{
    private const DEFAULT_JSON_SECRETS = 'JSON_SECRETS';

    /**
     * @param string[]|null $jsonSecrets
     */
    public static function loadEnvVars(?array $jsonSecrets = null): void
    {
        foreach ($jsonSecrets ?? [self::DEFAULT_JSON_SECRETS] as $jsonSecret) {
            foreach (\json_decode($_SERVER[$jsonSecret] ?? '', true) ?? [] as $name => $value) {
                OutputHelper::writeln(\sprintf('Loading env var %s from %s', $name, $jsonSecret));

                $_SERVER[$name] = $value;
                $_ENV[$name] = $value;
            }
        }

        // Handle env var substitution
        foreach (EnvVarSubstitutionHelper::resolveVariables($_SERVER) as $name => $value) {
            $_SERVER[$name] = $value;
        }

        foreach (EnvVarSubstitutionHelper::resolveVariables($_ENV) as $name => $value) {
            $_ENV[$name] = $value;
        }
    }
}
