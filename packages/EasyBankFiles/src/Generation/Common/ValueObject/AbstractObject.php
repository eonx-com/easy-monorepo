<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Generation\Common\ValueObject;

use EonX\EasyBankFiles\Common\ValueObject\AbstractDataBag;

abstract class AbstractObject extends AbstractDataBag
{
    public function __construct(?array $data = null)
    {
        parent::__construct(\array_merge([
            'recordType' => $this->initRecordType(),
        ], $data ?? []));
    }

    /**
     * @return array<string, \EonX\EasyBankFiles\Generation\Common\Enum\ValidationRule>
     */
    abstract public function getValidationRules(): array;

    /**
     * Return all the attributes.
     */
    public function getAttributes(): array
    {
        return $this->data;
    }

    /**
     * Return attribute values as single line.
     */
    public function getAttributesAsLine(): string
    {
        $line = [];
        $paddingRules = $this->getAttributesPaddingRules();

        foreach ($this->attributes as $attribute) {
            $value = $this->data[$attribute] ?? '';

            if (isset($paddingRules[$attribute])) {
                $paddingRulesForAttribute = (array)$paddingRules[$attribute];
                \array_unshift($paddingRulesForAttribute, (string)$value);

                $value = \str_pad(...$paddingRulesForAttribute);
            }

            $line[] = $value;
        }

        return \implode('', $line);
    }

    /**
     * Set the value of the attribute.
     *
     * @param string|int|null $value
     */
    public function setAttribute(string $attribute, $value = null): self
    {
        // Set value if null
        if ($value === null) {
            $value = '';
        }

        $this->data[$attribute] = $value;

        return $this;
    }

    /**
     * Get attributes padding configuration as [<attribute> => [<length>, <string>, <type>]].
     *
     * @see http://php.net/manual/en/function.str-pad.php
     */
    abstract protected function getAttributesPaddingRules(): array;

    /**
     * Return record type.
     */
    abstract protected function initRecordType(): string;
}
