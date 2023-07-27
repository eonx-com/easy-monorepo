<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Nai\Results\Groups;

use EonX\EasyBankFiles\Parsers\BaseResult;

/**
 * @method string getAsOfDate()
 * @method string getAsOfTime()
 * @method string getCode()
 * @method string getGroupStatus()
 * @method string getOriginatorReceiverId()
 * @method string getUltimateReceiverId()
 */
final class Header extends BaseResult
{
    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return ['asOfDate', 'asOfTime', 'code', 'groupStatus', 'originatorReceiverId', 'ultimateReceiverId'];
    }
}
