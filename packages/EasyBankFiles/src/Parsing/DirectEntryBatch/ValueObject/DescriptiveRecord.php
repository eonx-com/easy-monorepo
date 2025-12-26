<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\DirectEntryBatch\ValueObject;

use DateTimeImmutable;
use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string|null getDateProcessed()
 * @method string|null getDescriptionOfEntries()
 * @method string|null getReelSequenceNumber()
 * @method string|null getUserFinancialInstitution()
 * @method string|null getNumberOfUserSupplyingFile()
 * @method string|null getNameOfUserSupplyingFile()
 */
final class DescriptiveRecord extends AbstractResult
{
    private const string DATE_STRING_PATTERN = 'dmy';

    public function getDateProcessedObject(): ?DateTimeImmutable
    {
        $value = $this->data['dateProcessed'];

        if (\is_string($value) === true &&
            \strlen($value) === 6 &&
            \ctype_digit($value) === true
        ) {
            $dateTime = DateTimeImmutable::createFromFormat(self::DATE_STRING_PATTERN, $value);
            if ($dateTime !== false) {
                return $dateTime->setTime(0, 0);
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return [
            'dateProcessed',
            'descriptionOfEntries',
            'reelSequenceNumber',
            'userFinancialInstitution',
            'numberOfUserSupplyingFile',
            'nameOfUserSupplyingFile',
        ];
    }
}
