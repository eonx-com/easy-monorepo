<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\DirectEntry\Results;

use DateTime;
use DateTimeInterface;
use EonX\EasyBankFiles\Parsers\BaseResult;

/**
 * @method string|null getDateProcessed()
 * @method string|null getDescription()
 * @method string|null getUserFinancialInstitution()
 * @method string|null getUserIdSupplyingFile()
 * @method string|null getUserSupplyingFile()
 * @method string|null getReelSequenceNumber()
 */
final class Header extends BaseResult
{
    private const DATE_STRING_PATTERN = '%s-%s-%s';

    /**
     * Return processed date as a DateTime object.
     */
    public function getDateProcessedObject(): ?DateTimeInterface
    {
        $value = $this->data['dateProcessed'];

        if (
            \is_string($value) === true &&
            \strlen($value) === 6 &&
            \ctype_digit($value) === true
        ) {
            $stringDate = \sprintf(
                self::DATE_STRING_PATTERN,
                \substr($value, 4, 2),
                \substr($value, 2, 2),
                \substr($value, 0, 2)
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
