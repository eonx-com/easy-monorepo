<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Nai\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getAsOfDate()
 * @method string getAsOfTime()
 * @method string getCode()
 * @method string getGroupStatus()
 * @method string getOriginatorReceiverId()
 * @method string getUltimateReceiverId()
 */
final class GroupHeader extends AbstractResult
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
