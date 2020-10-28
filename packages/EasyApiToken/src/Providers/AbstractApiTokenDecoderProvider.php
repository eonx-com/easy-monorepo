<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Providers;

use EonX\EasyApiToken\Interfaces\ApiTokenDecoderProviderInterface;

abstract class AbstractApiTokenDecoderProvider implements ApiTokenDecoderProviderInterface
{
    /**
     * @var int
     */
    private $priority;

    public function __construct(?int $priority = null)
    {
        $this->priority = $priority ?? 0;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
