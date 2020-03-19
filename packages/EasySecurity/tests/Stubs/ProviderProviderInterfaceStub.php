<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasySecurity\Interfaces\ProviderInterface;
use EonX\EasySecurity\Interfaces\ProviderProviderInterface;

final class ProviderProviderInterfaceStub implements ProviderProviderInterface
{
    /**
     * @var null|\EonX\EasySecurity\Interfaces\ProviderInterface
     */
    private $provider;

    public function __construct(?ProviderInterface $provider = null)
    {
        $this->provider = $provider;
    }

    /**
     * @param int|string $uniqueId
     */
    public function getProvider($uniqueId): ?ProviderInterface
    {
        return $this->provider;
    }
}
