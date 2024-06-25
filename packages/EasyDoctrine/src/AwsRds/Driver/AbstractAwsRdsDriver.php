<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\AwsRds\Driver;

use Doctrine\DBAL\Driver;
use EonX\EasyDoctrine\AwsRds\Resolver\AwsRdsConnectionParamsResolver;

abstract class AbstractAwsRdsDriver
{
    public function __construct(
        protected readonly Driver $decorated,
        protected readonly AwsRdsConnectionParamsResolver $connectionParamsResolver,
    ) {
    }
}
