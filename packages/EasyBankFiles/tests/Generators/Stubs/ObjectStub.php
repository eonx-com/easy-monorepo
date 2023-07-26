<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Generators\Stubs;

use EonX\EasyBankFiles\Generators\BaseObject;

final class ObjectStub extends BaseObject
{
    /**
     * Get validation rules.
     *
     * @return mixed[]
     */
    public function getValidationRules(): array
    {
        return [];
    }

    /**
     * Get attributes padding configuration as [<attribute> => [<length>, <string>, <type>]].
     *
     * @return mixed[]
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
