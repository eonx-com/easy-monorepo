<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\DirectEntry\Results;

use DateTime;
use EonX\EasyBankFiles\Parsers\BaseResult;

/**
 * @method string|null getDescription()
 * @method string|null getUserFinancialInstitution()
 * @method string|null getUserIdSupplyingFile()
 * @method string|null getUserSupplyingFile()
 * @method string|null getReelSequenceNumber()
 */
final class Header extends BaseResult
{
    /**
     * @var string Date in string representation pattern
     */
    private const DATE_STRING_PATTERN = '%s-%s-%s';

    public function getDateProcessed(): ?DateTime
    {
        if (\is_string($this->data['dateProcessed']) === true &&
            \strlen($this->data['dateProcessed']) === 6 &&
            \ctype_digit($this->data['dateProcessed']) === true
        ) {
            $stringDate = \sprintf(
                self::DATE_STRING_PATTERN,
                \substr($this->data['dateProcessed'], 4, 2),
                \substr($this->data['dateProcessed'], 2, 2),
                \substr($this->data['dateProcessed'], 0, 2)
            );

            return new DateTime($stringDate);
        }

        return null;
    }

    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return [
            'dateProcessed',
            'description',
            'userFinancialInstitution',
            'userIdSupplyingFile',
            'userSupplyingFile',
            'reelSequenceNumber',
        ];
    }
}
