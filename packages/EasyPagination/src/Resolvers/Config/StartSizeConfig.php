<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPagination\Resolvers\Config;

use StepTheFkUp\EasyPagination\Interfaces\StartSizeConfigInterface;

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

    /**
     * StartSizeConfig constructor.
     *
     * @param string $startAttribute
     * @param int $startDefault
     * @param string $sizeAttribute
     * @param int $sizeDefault
     */
    public function __construct(string $startAttribute, int $startDefault, string $sizeAttribute, int $sizeDefault)
    {
        $this->startAttribute = $startAttribute;
        $this->startDefault = $startDefault;
        $this->sizeAttribute = $sizeAttribute;
        $this->sizeDefault = $sizeDefault;
    }

    /**
     * Get size attribute name.
     *
     * @return string
     */
    public function getSizeAttribute(): string
    {
        return $this->sizeAttribute;
    }

    /**
     * Get size attribute default value.
     *
     * @return int
     */
    public function getSizeDefault(): int
    {
        return $this->sizeDefault;
    }

    /**
     * Get start attribute name.
     *
     * @return string
     */
    public function getStartAttribute(): string
    {
        return $this->startAttribute;
    }

    /**
     * Get start attribute default value.
     *
     * @return int
     */
    public function getStartDefault(): int
    {
        return $this->startDefault;
    }
}

\class_alias(
    StartSizeConfig::class,
    'LoyaltyCorp\EasyPagination\Resolvers\Config\StartSizeConfig',
    false
);
