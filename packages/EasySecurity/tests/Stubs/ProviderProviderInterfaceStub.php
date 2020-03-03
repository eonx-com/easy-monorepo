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

    /**
     * ProviderProviderInterfaceStub constructor.
     *
     * @param null|\EonX\EasySecurity\Interfaces\ProviderInterface $provider
     */
    public function __construct(?ProviderInterface $provider = null)
    {
        $this->provider = $provider;
    }
    
    /**
     * Get provider for given uniqueId.
     *
     * @param int|string $uniqueId
     *
     * @return null|\EonX\EasySecurity\Interfaces\ProviderInterface
     */
    public function getProvider($uniqueId): ?ProviderInterface
    {
        return $this->provider;
    }
}
