<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Helper;

use EonX\EasySwoole\Logging\Helper\OutputHelper;
use EonX\EasyUtils\Common\Helper\EnvVarSubstitutionHelper;

use function Symfony\Component\String\u;

final class EnvVarHelper
{
    /**
     * @param string[] $jsonSecrets
     */
    public static function loadEnvVars(
        array $jsonSecrets,
        ?string $dotEnvPath = null,
        ?bool $outputEnabled = null,
    ): void {
        $jsonSecrets = \array_map(static function (string $jsonSecret): string {
            $jsonSecret = u($jsonSecret);

            if ($jsonSecret->startsWith('/') === false) {
                $jsonSecret = $jsonSecret
                    ->prepend('/')
                    ->append('/')
                    ->replace('\\\\', '\\');
            }

            return $jsonSecret->toString();
        }, $jsonSecrets);

        foreach (\array_keys($_SERVER) as $envVarName) {
            foreach ($jsonSecrets as $jsonSecret) {
                if (u($envVarName)->ignoreCase()->match($jsonSecret)) {
                    /** @var string $envVarRawValue */
                    $envVarRawValue = $_SERVER[$envVarName] ?? '';
                    /** @var array|null $envVarValue */
                    $envVarValue = \json_decode($envVarRawValue, true);
                    foreach ($envVarValue ?? [] as $name => $value) {
                        $name = u($name)
                            ->upper()
                            ->toString();

                        if ($outputEnabled ?? true) {
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

        if (($outputEnabled ?? true) && $dotEnvPath !== null && isset($_SERVER['SYMFONY_DOTENV_VARS'])) {
            /** @var scalar $symfonyDotEnvVars */
            $symfonyDotEnvVars = $_SERVER['SYMFONY_DOTENV_VARS'];
            foreach (\explode(',', (string)$symfonyDotEnvVars) as $name) {
                OutputHelper::writeln(\sprintf('Loading env var %s from %s', $name, $dotEnvPath));
            }
        }
    }
}
