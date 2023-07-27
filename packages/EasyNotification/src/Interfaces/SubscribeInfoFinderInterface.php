<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Interfaces;

interface SubscribeInfoFinderInterface
{
    /**
     * @param string[] $topics
     */
    public function find(string $apiKey, string $providerExternalId, array $topics): SubscribeInfoInterface;
}
