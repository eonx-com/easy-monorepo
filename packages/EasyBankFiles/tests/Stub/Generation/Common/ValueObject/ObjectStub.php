<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Stub\Generation\Common\ValueObject;

use EonX\EasyBankFiles\Generation\Common\ValueObject\AbstractObject;

final class ObjectStub extends AbstractObject
{
    public function getValidationRules(): array
    {
        return [];
    }

    /**
     * Get attributes padding configuration as [<attribute> => [<length>, <string>, <type>]].
     *
     * @see http://php.net/manual/en/function.str-pad.php
     */
    protected function getAttributesPaddingRules(): array
    {
        return [];
    }

    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return ['accountName', 'accountNumber'];
    }

    /**
     * Return record type.
     */
    protected function initRecordType(): string
    {
        return '1';
    }
}
