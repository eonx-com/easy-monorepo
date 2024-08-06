<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Generation\Common\Generator;

use DateTime;
use EonX\EasyBankFiles\Generation\Common\Enum\LineBreak;
use EonX\EasyBankFiles\Generation\Common\Enum\ValidationRule;
use EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException;
use EonX\EasyBankFiles\Generation\Common\Exception\LengthMismatchesException;
use EonX\EasyBankFiles\Generation\Common\Exception\ValidationFailedException;
use EonX\EasyBankFiles\Generation\Common\ValueObject\AbstractObject;

abstract class AbstractGenerator implements GeneratorInterface
{
    protected string $contents = '';

    protected LineBreak $lineBreak = LineBreak::Windows;

    /**
     * Return contents.
     */
    public function getContents(): string
    {
        $this->generate();

        return $this->contents;
    }

    public function setBreakLines(LineBreak $lineBreak): static
    {
        $this->lineBreak = $lineBreak;

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
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\LengthMismatchesException
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
     * @param array<string, \EonX\EasyBankFiles\Generation\Common\Enum\ValidationRule>|null $rules
     *
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\ValidationFailedException
     */
    protected function validateAttributes(AbstractObject $object, ?array $rules = null): void
    {
        $errors = [];

        foreach ($rules ?? [] as $attribute => $rule) {
            $this->processRule($errors, $rule, $attribute, (string)$object->{'get' . \ucfirst($attribute)}());
        }

        if (\count($errors) > 0) {
            throw new ValidationFailedException($errors, 'Validation Errors');
        }
    }

    /**
     * Add line to contents.
     *
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\LengthMismatchesException
     */
    protected function writeLine(string $line): void
    {
        $this->checkLineLength($line);
        $this->contents .= $line . $this->lineBreak->value;
    }

    /**
     * Add lines for given objects.
     *
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\ValidationFailedException
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\LengthMismatchesException
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException
     */
    protected function writeLinesForObjects(array $objects): void
    {
        foreach ($objects as $object) {
            if (($object instanceof AbstractObject) === false) {
                throw new InvalidArgumentException(\sprintf(
                    'Object must be %s, %s given.',
                    AbstractObject::class,
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
     * @param array $errors The errors array to set errors to
     * @param \EonX\EasyBankFiles\Generation\Common\Enum\ValidationRule $rule The rule to process
     * @param string $attribute The attribute the value relates to
     */
    private function processRule(array &$errors, ValidationRule $rule, string $attribute, mixed $value): void
    {
        // Not sure why we allow arrays here
        if ($value === null || $value === '' || (\is_array($value) && \count($value) === 0)) {
            $errors[] = [
                ...\compact('attribute', 'value'),
                'rule' => ValidationRule::Required,
            ];

            return;
        }

        // Not sure why we would have anything else than a string value here
        if (\is_string($value) === false) {
            return;
        }

        switch ($rule) {
            case ValidationRule::Bsb:
                // 123-456 length must be 7 characters with '-' in the 4th position
                if (\preg_match(ValidationRule::Bsb->value, $value) === 0) {
                    $errors[] = \compact('attribute', 'value', 'rule');
                }

                break;

            case ValidationRule::Date:
                if (DateTime::createFromFormat('dmy', $value) === false &&
                    DateTime::createFromFormat('Ymd', $value) === false) {
                    $errors[] = \compact('attribute', 'value', 'rule');
                }

                break;

            case ValidationRule::Alpha:
                if (\preg_match(ValidationRule::Alpha->value, $value)) {
                    $errors[] = \compact('attribute', 'value', 'rule');
                }

                break;
            case ValidationRule::Numeric:
                if (\preg_match(ValidationRule::Numeric->value, $value)) {
                    $errors[] = \compact('attribute', 'value', 'rule');
                }

                break;
        }
    }
}
