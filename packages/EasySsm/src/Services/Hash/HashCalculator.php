<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Hash;

use EonX\EasySsm\Helpers\Parameters;
use Nette\Utils\Json;

final class HashCalculator implements HashCalculatorInterface
{
    /**
     * @var \EonX\EasySsm\Helpers\Parameters
     */
    private $parametersHelper;

    public function __construct(Parameters $parametersHelper)
    {
        $this->parametersHelper = $parametersHelper;
    }

    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $parameters
     *
     * @throws \Nette\Utils\JsonException
     */
    public function calculate(array $parameters): string
    {
        $array = $this->parametersHelper->toKeyObjectAsStrings($parameters);

        \ksort($array);

        return \md5(Json::encode($array));
    }
}
