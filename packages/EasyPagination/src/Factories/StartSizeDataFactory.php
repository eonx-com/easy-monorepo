<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Factories;

use EonX\EasyPagination\Data\StartSizeData;
use EonX\EasyPagination\Interfaces\StartSizeDataFactoryInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;

final class StartSizeDataFactory implements StartSizeDataFactoryInterface
{
    /**
     * @var \EonX\EasyPagination\Interfaces\StartSizeDataInterface
     */
    private $default;

    public function __construct(StartSizeDataInterface $default)
    {
        $this->default = $default;
    }

    public function create(
        ?int $start = null,
        ?int $size = null,
        ?string $startAttr = null,
        ?string $sizeAttr = null,
        ?string $url = null
    ): StartSizeDataInterface {
        return new StartSizeData(
            $start ?? $this->default->getStart(),
            $size ?? $this->default->getSize(),
            $startAttr ?? $this->default->getStartAttribute(),
            $sizeAttr ?? $this->default->getSizeAttribute(),
            $url ?? $this->default->getUrl()
        );
    }
}
