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
use Symfony\Component\Finder\Finder;

final class ErrorCodesFromEnumProvider implements ErrorCodesProviderInterface
{
    private Parser $parser;

    public function __construct(
        private readonly string $projectDir,
    ) {
        $this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
    }

    /**
     * @return array<mixed>
     */
    public function locateErrorCodesEnums()
    {
        $files = (new Finder())
            ->in($this->projectDir)
            ->name('*.php')
            ->exclude(['vendor', 'var', 'tests'])
            ->files();

        $enums = [];
        foreach ($files as $file) {
            $fqcn = (string)$this->extractFqcn($file->getRealPath());
            if (\class_exists($fqcn) && \count((new ReflectionClass($fqcn))->getAttributes(AsErrorCodes::class)) > 0) {
                $enums[] = $fqcn;
            }
        }

        return $enums;
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
                $splitName = \preg_split(
                    pattern: '/([A-Z\d][a-z\d]+)/u',
                    subject: (string)$case->name,
                    flags: \PREG_SPLIT_DELIM_CAPTURE
                );
                if (\is_array($splitName)) {
                    $errorCodes[] = new ErrorCodeDto(
                        originalName: $case->name,
                        errorCode: $case->value,
                        splitName: \array_filter($splitName, static fn ($value): bool => $value !== '')
                    );
                }
            }
        }

        return $errorCodes;
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
        } catch (Error) {
            // Ignore
        }

        return null;
    }
}
