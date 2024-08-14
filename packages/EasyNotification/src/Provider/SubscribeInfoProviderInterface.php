<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Provider;

use EonX\EasyNotification\ValueObject\SubscribeInfo;

interface SubscribeInfoProviderInterface
{
    /**
     * @param string[] $topics
     */
    public function provide(string $apiKey, string $providerExternalId, array $topics): SubscribeInfo;
}
