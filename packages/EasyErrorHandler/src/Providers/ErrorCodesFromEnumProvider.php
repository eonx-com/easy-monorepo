<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Providers;

use EonX\EasyErrorHandler\Annotations\AsErrorCodes;
use EonX\EasyErrorHandler\DataTransferObjects\ErrorCodeDto;
use EonX\EasyErrorHandler\Interfaces\ErrorCodesProviderInterface;
use PhpParser\Error;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use ReflectionClass;

final class ErrorCodesFromEnumProvider implements ErrorCodesProviderInterface
{
    private Parser $parser;

    public function __construct(private readonly string $projectDir)
    {
        $this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
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
                $splittedName = \preg_split(
                    pattern: '/([A-Z\d][a-z\d]+)/u',
                    subject: $case->name,
                    flags: \PREG_SPLIT_DELIM_CAPTURE
                );
                if (\is_array($splittedName)) {
                    $errorCodes[] = new ErrorCodeDto(
                        originalName: $case->name,
                        errorCode: $case->value,
                        splitName: \array_filter($splittedName, static fn ($value) => $value !== '')
                    );
                }
            }
        }

        return $errorCodes;
    }

    /**
     * @return array<mixed>
     */
    public function locateErrorCodesEnums()
    {
        $directory = new \RecursiveDirectoryIterator($this->projectDir);
        $iterator = new \RecursiveIteratorIterator($directory);
        $regex = new \RegexIterator($iterator, '/\.php$/');

        $enums = [];
        foreach ($regex as $file) {
            $fqcn = (string)$this->extractFqcn($file->getRealPath());
            if (\class_exists($fqcn) && \count((new ReflectionClass($fqcn))->getAttributes(AsErrorCodes::class)) > 0) {
                $enums[] = $fqcn;
            }
        }

        return $enums;
    }

    private function extractFqcn(string $file): ?string
    {
        try {
            $code = (string)\file_get_contents($file);
            if (\str_contains($code, AsErrorCodes::class) === false) {
                return null;
            }
            $stmts = $this->parser->parse($code);
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
