<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Nai\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

abstract class AbstractNaiResult extends AbstractResult
{
    public function __construct(
        protected ResultsContextInterface $context,
        ?array $data = null,
    ) {
        parent::__construct($data);
    }
}
