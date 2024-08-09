<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\AwsRds\Provider;

interface AwsRdsAuthTokenProviderInterface
{
    public function provide(array $params): string;
}
