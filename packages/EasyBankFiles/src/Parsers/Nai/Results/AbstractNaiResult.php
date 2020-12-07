<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Nai\Results;

use EonX\EasyBankFiles\Parsers\BaseResult;

abstract class AbstractNaiResult extends BaseResult
{
    /**
     * @var \EonX\EasyBankFiles\Parsers\Nai\Results\ResultsContext
     */
    protected $context;

    /**
     * AbstractNaiResult constructor.
     *
     * @param mixed[]|null $data
     */
    public function __construct(ResultsContext $context, ?array $data = null)
    {
        parent::__construct($data);

        $this->context = $context;
    }
}
