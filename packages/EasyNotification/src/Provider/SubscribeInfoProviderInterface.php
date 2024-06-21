<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Provider;

use EonX\EasyNotification\ValueObject\SubscribeInfoInterface;

interface SubscribeInfoProviderInterface
{
    /**
     * @param string[] $topics
     */
    public function find(string $apiKey, string $providerExternalId, array $topics): SubscribeInfoInterface;
}
