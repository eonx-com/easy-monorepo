<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Nai\Results;

use EonX\EasyBankFiles\Parsers\BaseResult;

abstract class AbstractNaiResult extends BaseResult
{
    /**
     * @param mixed[]|null $data
     */
    public function __construct(
        protected ResultsContextInterface $context,
        ?array $data = null,
    ) {
        parent::__construct($data);
    }
}
