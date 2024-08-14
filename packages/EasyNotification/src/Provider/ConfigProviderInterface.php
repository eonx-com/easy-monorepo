<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Provider;

use EonX\EasyNotification\ValueObject\Config;

interface ConfigProviderInterface
{
    public function provide(string $apiKey, string $providerExternalId): Config;
}
