<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Helpers;

class EnvVarSubstitutionHelper
{
    private const VAR_NAME_REGEX = '(?i:[A-Z][A-Z0-9_]*+)';

    private const VAR_REGEX = '/
            (?<!\\\\)
            (?P<backslashes>\\\\*)              # escaped with a backslash?
            \$
            (?!\()                              # no opening parenthesis
            (?P<opening_brace>\{)?              # optional brace
            (?P<name>' . self::VAR_NAME_REGEX . ')? # var name
            (?P<default_value>:[-=][^\}]++)?    # optional default value
            (?P<closing_brace>\})?              # optional closing brace
        /x';

    private static int $maxAttempts = 100;

    private static int $unresolved = 0;

    /**
     * @var string[]
     */
    private static array $values = [];

    /**
     * @param array<string, mixed> $envs
     *
     * @return array<string, mixed>
     */
    public static function resolveVariables(array $envs): array
    {
        \ksort($envs);

        self::$values = [];
        $currentAttempt = 0;

        do {
            self::$unresolved = 0;

            foreach ($envs as $name => $value) {
                self::$values[$name] = \is_string($value) ? self::doResolveVariables($value) : $value;
            }

            $currentAttempt++;
        } while (self::$unresolved > 0 && $currentAttempt < self::$maxAttempts);

        return self::$values;
    }

    public static function setMaxAttempts(int $maxAttempts): void
    {
        self::$maxAttempts = $maxAttempts;
    }

    private static function doResolveVariables(string $value): string
    {
        if (\str_contains($value, '$') === false) {
            return $value;
        }

        return (string)\preg_replace_callback(self::VAR_REGEX, function ($matches) {
            // odd number of backslashes means the $ character is escaped
            if (\strlen($matches['backslashes']) % 2 === 1) {
                return \substr($matches[0], 1);
            }

            // unescaped $ not followed by variable name
            if (isset($matches['name']) === false) {
                return $matches[0];
            }

            if ($matches['opening_brace'] === '{' && isset($matches['closing_brace']) === false) {
                throw new \RuntimeException('Unclosed braces on variable expansion');
            }

            $name = $matches['name'];
            $value = self::resolveEnvVarValue($name);

            if ($value === '' && isset($matches['default_value']) && $matches['default_value'] !== '') {
                $unsupportedChars = \strpbrk($matches['default_value'], '\'"{$');

                if (\is_string($unsupportedChars)) {
                    throw new \RuntimeException(\sprintf(
                        'Unsupported character "%s" found in the default value of variable "$%s".',
                        $unsupportedChars[0],
                        $name
                    ));
                }

                $value = \substr($matches['default_value'], 2);

                if ($matches['default_value'][1] === '=') {
                    self::$values[$name] = $value;
                }
            }

            if ($matches['opening_brace'] === '' && isset($matches['closing_brace'])) {
                $value .= '}';
            }

            if ($value === '') {
                self::$unresolved++;
                $value = $matches[0];
            }

            return $matches['backslashes'] . $value;
        }, $value);
    }

    private static function resolveEnvVarValue(string $name): string
    {
        if (isset($_ENV[$name])) {
            return $_ENV[$name];
        }

        if (isset($_SERVER[$name]) && \str_contains($name, 'HTTP_')) {
            return $_SERVER[$name];
        }

        return (string)(self::$values[$name] ?? \getenv($name));
    }
}
