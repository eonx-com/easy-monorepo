<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Resolvers\Config;

final class PagePaginationConfig
{
    /**
     * @var string
     */
    private $numberAttr;

    /**
     * @var int
     */
    private $numberDefault;

    /**
     * @var string
     */
    private $sizeAttr;

    /**
     * @var int
     */
    private $sizeDefault;

    /**
     * PagePaginationConfig constructor.
     *
     * @param string $numberAttr
     * @param int $numberDefault
     * @param string $sizeAttr
     * @param int $sizeDefault
     */
    public function __construct(string $numberAttr, int $numberDefault, string $sizeAttr, int $sizeDefault)
    {
        $this->numberAttr = $numberAttr;
        $this->numberDefault = $numberDefault;
        $this->sizeAttr = $sizeAttr;
        $this->sizeDefault = $sizeDefault;
    }

    /**
     * Get page number attribute.
     *
     * @return string
     */
    public function getNumberAttr(): string
    {
        return $this->numberAttr;
    }

    /**
     * Get page number default.
     *
     * @return int
     */
    public function getNumberDefault(): int
    {
        return $this->numberDefault;
    }

    /**
     * Get page size attribute.
     *
     * @return string
     */
    public function getSizeAttr(): string
    {
        return $this->sizeAttr;
    }

    /**
     * Get page size default.
     *
     * @return int
     */
    public function getSizeDefault(): int
    {
        return $this->sizeDefault;
    }
}