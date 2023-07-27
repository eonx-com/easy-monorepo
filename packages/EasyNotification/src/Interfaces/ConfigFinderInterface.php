<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Interfaces;

interface ConfigFinderInterface
{
    public function find(string $apiKey, string $providerExternalId): ConfigInterface;
}
