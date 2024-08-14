<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Fixture\App\Provider;

use EonX\EasyDoctrine\AwsRds\Provider\AwsRdsAuthTokenProviderInterface;

final class DummyAwsRdsAuthTokenProvider implements AwsRdsAuthTokenProviderInterface
{
    public function provide(array $params): string
    {
        return 'dummy-auth-token';
    }
}
