<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Generators;

use DateTime;
use EonX\EasyBankFiles\Generators\Exceptions\InvalidArgumentException;
use EonX\EasyBankFiles\Generators\Exceptions\LengthMismatchesException;
use EonX\EasyBankFiles\Generators\Exceptions\ValidationFailedException;
use EonX\EasyBankFiles\Generators\Interfaces\GeneratorInterface;

abstract class BaseGenerator implements GeneratorInterface
{
    /**
     * @var string
     */
    protected $breakLine = self::BREAK_LINE_WINDOWS;

    /**
     * @var string
     */
    protected $contents = '';

    /**
     * @var string[] $validationRules
     */
    private static $validationRules = [
        self::VALIDATION_RULE_ALPHA => '/[^A-Za-z0-9 &\',-\.\/\+\$\!%\(\)\*\#=:\?\[\]_\^@]/',
        self::VALIDATION_RULE_NUMERIC => '/[^0-9-]/',
        self::VALIDATION_RULE_BSB => '/^\d{3}(\-)\d{3}/',
    ];

    /**
     * Return contents.
     */
    public function getContents(): string
    {
        $this->generate();

        return $this->contents;
    }

    /**
     * Set break lines.
     *
     * @return \EonX\EasyBankFiles\Generators\BaseGenerator
     */
    public function setBreakLines(string $breakLine): self
    {
        $this->breakLine = $breakLine;

        return $this;
    }

    /**
     * Generate.
     */
    abstract protected function generate(): void;

    /**
     * Return the defined line length of a generator.
     */
    abstract protected function getLineLength(): int;

    /**
     * Check if line's length is greater than defined length.
     *
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\LengthMismatchesException
     */
    protected function checkLineLength(string $line): void
    {
        if (\strlen($line) !== $this->getLineLength()) {
            throw new LengthMismatchesException(\sprintf(
                'Length %s mismatches the defined %s maximum characters',
                \strlen($line),
                $this->getLineLength()
            ));
        }
    }

    /**
     * Validate object attributes.
     *
     * @param null|mixed[] $rules
     *
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\ValidationFailedException
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     */
    protected function validateAttributes(BaseObject $object, ?array $rules = null): void
    {
        $errors = [];

        foreach ($rules ?? [] as $attribute => $rule) {
            $this->processRule($errors, $rule, $attribute, (string)$object->{'get' . \ucfirst($attribute)}());
        }

        if (\count($errors)) {
            throw new ValidationFailedException($errors, 'Validation Errors');
        }
    }

    /**
     * Add line to contents.
     *
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\LengthMismatchesException
     */
    protected function writeLine(string $line): void
    {
        $this->checkLineLength($line);
        $this->contents .= $line . $this->breakLine;
    }

    /**
     * Add lines for given objects.
     *
     * @param mixed[] $objects
     *
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\ValidationFailedException
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\LengthMismatchesException
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\InvalidArgumentException
     */
    protected function writeLinesForObjects(array $objects): void
    {
        foreach ($objects as $object) {
            if (($object instanceof BaseObject) === false) {
                throw new InvalidArgumentException(\sprintf(
                    'Object must be %s, %s given.',
                    BaseObject::class,
                    \gettype($object)
                ));
            }

            $this->validateAttributes($object, $object->getValidationRules());
            $this->writeLine($object->getAttributesAsLine());
        }
    }

    /**
     * Process rule against a value.
     *
     * @param mixed[] $errors The errors array to set errors to
     * @param string $rule The rule to process
     * @param string $attribute The attribute the value relates to
     * @param mixed $value The value from the attribute
     */
    private function processRule(array &$errors, string $rule, string $attribute, mixed $value): void
    {
        // Not sure why we allow arrays here...
        if ($value === null || $value === '' || (\is_array($value) && \count($value) === 0)) {
            $errors[] = \array_merge(\compact('attribute', 'value'), [
                'rule' => 'required',
            ]);

            return;
        }

        // Not sure why we would have anything else than a string value here...
        if (\is_string($value) === false) {
            return;
        }

        switch ($rule) {
            case self::VALIDATION_RULE_BSB:
                // 123-456 length must be 7 characters with '-' in the 4th position
                if (\preg_match(self::$validationRules[$rule], $value) === 0) {
                    $errors[] = \compact('attribute', 'value', 'rule');
                }
                break;

            case self::VALIDATION_RULE_DATE:
                if (DateTime::createFromFormat('dmy', $value) === false &&
                    DateTime::createFromFormat('Ymd', $value) === false) {
                    $errors[] = \compact('attribute', 'value', 'rule');
                }
                break;

            case self::VALIDATION_RULE_ALPHA:
            case self::VALIDATION_RULE_NUMERIC:
                if (\preg_match(self::$validationRules[$rule], $value)) {
                    $errors[] = \compact('attribute', 'value', 'rule');
                }
                break;
        }
    }
}
