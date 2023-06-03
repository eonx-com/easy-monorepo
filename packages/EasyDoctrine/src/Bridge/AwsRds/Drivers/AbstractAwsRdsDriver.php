<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\AwsRds\Drivers;

use Doctrine\DBAL\Driver;
use EonX\EasyDoctrine\Bridge\AwsRds\AwsRdsConnectionParamsResolver;

abstract class AbstractAwsRdsDriver
{
    public function __construct(
        protected readonly Driver $decorated,
        protected readonly AwsRdsConnectionParamsResolver $connectionParamsResolver
    ) {
    }
}
