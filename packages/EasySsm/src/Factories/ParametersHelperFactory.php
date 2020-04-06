<?php

declare(strict_types=1);

namespace EonX\EasySsm\Factories;

use EonX\EasySsm\Helpers\Parameters;

final class ParametersHelperFactory
{
    public function create(): Parameters
    {
        return new Parameters();
    }
}
