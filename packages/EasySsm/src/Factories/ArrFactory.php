<?php

declare(strict_types=1);

namespace EonX\EasySsm\Factories;

use EonX\EasySsm\Helpers\Arr;

final class ArrFactory
{
    public function create(): Arr
    {
        return new Arr();
    }
}
