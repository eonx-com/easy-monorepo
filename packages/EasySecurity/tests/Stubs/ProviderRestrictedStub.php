<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasySecurity\Interfaces\ProviderRestrictedInterface;

final class ProviderRestrictedStub implements ProviderRestrictedInterface
{
    /**
     * @var null|int|string
     */
    private $providerId;

    /**
     * @param null|int|string $providerId
     */
    public function __construct($providerId = null)
    {
        $this->providerId = $providerId;
    }

    /**
     * @return null|int|string
     */
    public function getRestrictedProviderUniqueId()
    {
        return $this->providerId;
    }
}
