<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Providers;

use EonX\EasyErrorHandler\Annotations\AsErrorCodes;
use EonX\EasyErrorHandler\Interfaces\ErrorCodesProviderInterface;
use PhpParser\Error;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use ReflectionClass;

final class ErrorCodesByEnumProvider implements ErrorCodesProviderInterface
{
    public function __construct(private string $projectDir)
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
                $errorCodes[$case->name] = $case->value;
            }
        }

        return $errorCodes;
    }

    /**
     * @return array<mixed>
     */
    private function locateErrorCodesEnums()
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);

        $directory = new \RecursiveDirectoryIterator($this->projectDir . '/src');
        $iterator = new \RecursiveIteratorIterator($directory);
        $regex = new \RegexIterator($iterator, '/Enum\/.*Enum\.php$/');

        $enums = [];

        foreach ($regex as $file) {
            $fqcn = (string)$this->extractFqcn($parser, $file->getRealPath());
            if (\class_exists($fqcn) && \count((new ReflectionClass($fqcn))->getAttributes(AsErrorCodes::class)) > 0) {
                $enums[] = $fqcn;
            }
        }

        return $enums;
    }

    private function extractFqcn(Parser $parser, string $file): ?string
    {
        try {
            $code = \file_get_contents($file);
            $stmts = $parser->parse((string)$code);
            foreach ((array)$stmts as $stmt) {
                if ($stmt instanceof Namespace_) {
                    $namespace = (string)$stmt->name;
                    foreach ($stmt->stmts as $namespaceStmt) {
                        if ($namespaceStmt instanceof Enum_) {
                            return $namespace . '\\' . $namespaceStmt->name;
                        }
                    }
                }
            }
        } catch(Error $e) {
            // ignore
        }

        return null;
    }
}
