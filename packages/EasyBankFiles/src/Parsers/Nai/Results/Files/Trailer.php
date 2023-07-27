<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Nai\Results\Files;

use EonX\EasyBankFiles\Parsers\BaseResult;
use EonX\EasyBankFiles\Parsers\Nai\ControlTotal;

/**
 * @method string getCode()
 * @method string getNumberOfGroups()
 * @method string getNumberOfRecords()
 */
final class Trailer extends BaseResult
{
    use ControlTotal;

    /**
     * Return file control total A.
     */
    public function getFileControlTotalA(): float
    {
        return $this->formatAmount($this->data['fileControlTotalA']);
    }

    /**
     * Return file control total B.
     */
    public function getFileControlTotalB(): float
    {
        return $this->formatAmount($this->data['fileControlTotalB']);
    }

    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return ['code', 'fileControlTotalA', 'fileControlTotalB', 'numberOfGroups', 'numberOfRecords'];
    }
}
