<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Sniffs\Namespaces;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\ClassHelper;
use SlevomatCodingStandard\Helpers\NamespaceHelper;

final class Psr4Sniff implements Sniff
{
    /**
     * @var string
     */
    public const CODE_NAMESPACE_VIOLATION = 'PSR4Namespace';

    /**
     * @var string
     */
    public const CODE_NO_COMPOSER_AUTOLOAD_DEFINED = 'NoComposerAutoloadDefined';

    /**
     * @var string
     */
    public $composerJsonPath = 'composer.json';

    /**
     * @var mixed[]
     */
    private static $composerContents = [];

    /**
     * @var string
     */
    private $code = '';

    /**
     * @var string
     */
    private $expectedNamespace = '';

    /**
     * @var \PHP_CodeSniffer\Files\File
     */
    private $phpcsFile;

    /**
     * @param int $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $this->phpcsFile = $phpcsFile;

        $classFqn = ClassHelper::getFullyQualifiedName($phpcsFile, $stackPtr);

        if ($this->isPsr4Compliant($classFqn) === true || $this->isPsr4Compliant($classFqn, true) === true) {
            return;
        }

        $this->addError((int)$stackPtr);
    }

    /**
     * @return mixed[]
     */
    public function register(): array
    {
        return [\T_CLASS, \T_INTERFACE, \T_TRAIT];
    }

    private function addError(int $openPointer): void
    {
        if ($this->code === self::CODE_NO_COMPOSER_AUTOLOAD_DEFINED) {
            $message = \sprintf('No autoload entries found in %s.', $this->composerJsonPath);
            $this->phpcsFile->addError($message, (int)$this->phpcsFile->findNext(\T_NAMESPACE, 0), $this->code);

            return;
        }
        $message = \sprintf(
            'Namespace name does not match PSR-4 project structure. It should be `%s` instead of `%s`.',
            $this->expectedNamespace,
            NamespaceHelper::findCurrentNamespaceName($this->phpcsFile, $openPointer)
        );

        $this->phpcsFile->addError($message, (int)$this->phpcsFile->findNext(\T_NAMESPACE, 0), $this->code);
    }

    /**
     * @return mixed[]
     */
    private function getComposerContents(): array
    {
        if (\count(self::$composerContents) > 0) {
            return self::$composerContents;
        }

        $basePath = $this->phpcsFile->config !== null ? $this->phpcsFile->config->getSettings()['basepath'] : '';

        $composerFile = $basePath . $this->composerJsonPath;

        return self::$composerContents = \json_decode((string)\file_get_contents($composerFile), true);
    }

    private function isPsr4Compliant(string $classFqn, ?bool $isDev = null): bool
    {
        $psr4s = $this->getComposerContents()[\sprintf('autoload%s', $isDev === true ? '-dev' : '')]['psr-4'] ?? [];

        if (empty($psr4s) === true) {
            $this->code = self::CODE_NO_COMPOSER_AUTOLOAD_DEFINED;

            return false;
        }

        $classFilename = $this->phpcsFile->getFilename();

        foreach ($psr4s as $baseNamespace => $basePath) {
            $basePathPosition = \strpos($classFilename, $basePath);

            if ($basePathPosition === false) {
                continue;
            }

            // Convert $classFqn to be similar to $classFilename. \Base\Namespace\To\Class to base/path/src/to/Class
            $testPath = \str_replace(
                [\trim($baseNamespace, '\\') . '\\', '\\'],
                [\trim($basePath, '/') . '/', '/'],
                \trim($classFqn, '\\')
            );

            if (\strpos($classFilename, $testPath) !== false) {
                return true;
            }

            $relativePath = \substr(\dirname($classFilename), $basePathPosition, \strlen($classFilename));

            $this->expectedNamespace = \str_replace(
                [$basePath, '/'],
                [$baseNamespace, '\\'],
                $relativePath
            );
        }

        $this->code = self::CODE_NAMESPACE_VIOLATION;

        return false;
    }
}
