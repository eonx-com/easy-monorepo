<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\AwsRds\Provider;

use Aws\Credentials\CredentialsInterface;

interface AwsRdsAuthTokenCredentialsProviderInterface
{
    public function provide(string $awsRegion, array $params): callable|CredentialsInterface;
}
