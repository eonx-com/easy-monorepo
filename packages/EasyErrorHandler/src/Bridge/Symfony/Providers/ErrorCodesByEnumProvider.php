<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Providers;

use EonX\EasyErrorHandler\Interfaces\ErrorCodesEnumInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorCodesProviderInterface;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

final class ErrorCodesByEnumProvider implements ErrorCodesProviderInterface
{
    private const ERROR_CODE_NAME_PREFIX = 'Error';

    public function __construct(private readonly string $projectDir)
    {
    }

    public function provide(): array
    {
        $enums = $this->locateErrorCodesEnums();

        if (\count($enums) === 0) {
            return [];
        }

        $errorCodes = [];
        foreach ($enums as $enum) {
            $cases = $enum::cases();
            foreach ($cases as $case) {
                if (\str_starts_with($case->name, self::ERROR_CODE_NAME_PREFIX) === true) {
                    $errorCodes[$case->name] = $case->value;
                }
            }
        }

        return $errorCodes;
    }

    /**
     * @return array<mixed>
     */
    private function locateErrorCodesEnums(): array
    {
        $files = (new Finder())
            ->in($this->projectDir . '/src')
            ->path('/Enum/')
            ->name('*Enum.php')
            ->files();
        $enums = [];
        foreach ($files as $file) {
            $class = $this->extractNamespace($file->getRealPath());
            if (\class_exists($class) &&
                (new ReflectionClass($class))->implementsInterface(ErrorCodesEnumInterface::class) === true) {
                $enums[] = $class;
            }
        }
        return $enums;
    }

    private function extractNamespace(string $file): string
    {
        $contents = \file_get_contents($file);
        $class = '';
        $namespace = '';
        $gettingClass = false;
        $gettingNamespace = false;

        foreach (\token_get_all((string)$contents) as $token) {
            if (\is_array($token) && $token[0] === \T_NAMESPACE) {
                $gettingNamespace = true;
            }

            if (\is_array($token) && $token[0] === \T_ENUM) {
                $gettingClass = true;
            }

            if ($gettingNamespace === true) {
                if (\is_array($token) && \in_array($token[0], [\T_STRING, \T_NS_SEPARATOR, \T_NAME_QUALIFIED], true)) {
                    $namespace .= $token[1];
                }
                if ($token === ';') {
                    $gettingNamespace = false;
                }
            }

            if ($gettingClass === true) {
                if (\is_array($token) && $token[0] === \T_STRING) {
                    $class = $token[1];
                    break;
                }
            }
        }

        return $namespace ? $namespace . '\\' . $class : $class;
    }
}
