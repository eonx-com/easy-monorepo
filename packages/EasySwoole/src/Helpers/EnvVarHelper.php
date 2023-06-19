<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Helpers;

use EonX\EasyUtils\Helpers\EnvVarSubstitutionHelper;

use function Symfony\Component\String\u;

final class EnvVarHelper
{
    private const DEFAULT_JSON_SECRETS = 'SECRET_.+';

    private static bool $outputEnabled = true;

    public static function disableOutput(): void
    {
        self::$outputEnabled = false;
    }

    /**
     * @param string[]|null $jsonSecrets
     */
    public static function loadEnvVars(?array $jsonSecrets = null): void
    {
        $jsonSecrets = \array_map(static function (string $jsonSecret): string {
            $jsonSecret = u($jsonSecret);

            if ($jsonSecret->startsWith('/') === false) {
                $jsonSecret = $jsonSecret
                    ->prepend('/')
                    ->append('/')
                    ->replace('\\\\', '\\');
            }

            return $jsonSecret->toString();
        }, $jsonSecrets ?? [self::DEFAULT_JSON_SECRETS]);

        foreach (\array_keys($_SERVER) as $envVarName) {
            foreach ($jsonSecrets as $jsonSecret) {
                if (u($envVarName)->ignoreCase()->match($jsonSecret)) {
                    foreach (\json_decode($_SERVER[$envVarName] ?? '', true) ?? [] as $name => $value) {
                        $name = u($name)
                            ->upper()
                            ->toString();

                        if (self::$outputEnabled) {
                            OutputHelper::writeln(\sprintf('Loading env var %s from %s', $name, $envVarName));
                        }

                        $_SERVER[$name] = $value;
                        $_ENV[$name] = $value;
                    }

                    unset($_SERVER[$envVarName]);
                }
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
