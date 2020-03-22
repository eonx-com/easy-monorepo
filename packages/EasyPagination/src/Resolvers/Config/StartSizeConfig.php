<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Resolvers\Config;

use EonX\EasyPagination\Interfaces\StartSizeConfigInterface;

final class StartSizeConfig implements StartSizeConfigInterface
{
    /**
     * @var string
     */
    private $sizeAttribute;

    /**
     * @var int
     */
    private $sizeDefault;

    /**
     * @var string
     */
    private $startAttribute;

    /**
     * @var int
     */
    private $startDefault;

    public function __construct(string $startAttribute, int $startDefault, string $sizeAttribute, int $sizeDefault)
    {
        $this->startAttribute = $startAttribute;
        $this->startDefault = $startDefault;
        $this->sizeAttribute = $sizeAttribute;
        $this->sizeDefault = $sizeDefault;
    }

    public function getSizeAttribute(): string
    {
        return $this->sizeAttribute;
    }

    public function getSizeDefault(): int
    {
        return $this->sizeDefault;
    }

    public function getStartAttribute(): string
    {
        return $this->startAttribute;
    }

    public function getStartDefault(): int
    {
        return $this->startDefault;
    }
}
