<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Providers;

use EonX\EasyErrorHandler\Interfaces\ErrorCodesEnumInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorCodesProviderInterface;
use PhpParser\Error;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Parser;
use PhpParser\ParserFactory;

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
    private function locateErrorCodesEnums(): array
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);

        $directory = new \RecursiveDirectoryIterator($this->projectDir . '/src');
        $iterator = new \RecursiveIteratorIterator($directory);
        $regex = new \RegexIterator($iterator, '/Enum\/.*Enum\.php$/');

        $enums = [];
        foreach ($regex as $file) {
            $class = $this->extractNamespace($parser, $file->getRealPath());
            if ($class !== null) {
                $enums[] = $class;
            }
        }

        return $enums;
    }

    private function extractNamespace(Parser $parser, string $file): ?string
    {
        try {
            $code = \file_get_contents($file);
            $stmts = $parser->parse((string)$code);
            $interfaceFound = false;
            foreach ((array)$stmts as $stmt) {
                if ($stmt instanceof Namespace_) {
                    $namespace = (string)$stmt->name;
                    foreach ($stmt->stmts as $namespaceStmt) {
                        if ($namespaceStmt instanceof Use_) {
                            $errorCodeEnumInterface = \array_filter(
                                $namespaceStmt->uses,
                                fn (UseUse $useUse) => (string)$useUse->name === ErrorCodesEnumInterface::class
                            );
                            if (\count($errorCodeEnumInterface) > 0) {
                                $interfaceFound = true;
                            }
                        }
                        if ($interfaceFound && $namespaceStmt instanceof Enum_) {
                            $implements = $namespaceStmt->implements;
                            foreach ($implements as $impl) {
                                if (\str_ends_with($impl->toString(), 'ErrorCodesEnumInterface')) {
                                    return $namespace . '\\' . $namespaceStmt->name;
                                }
                            }
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
