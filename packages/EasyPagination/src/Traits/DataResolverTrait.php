<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Traits;

use EonX\EasyPagination\Data\StartSizeData;
use EonX\EasyPagination\Interfaces\StartSizeConfigInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;

trait DataResolverTrait
{
    /**
     * Create page pagination data for given data and configuration.
     *
     * @param \EonX\EasyPagination\Interfaces\StartSizeConfigInterface $config
     * @param mixed $data
     *
     * @return \EonX\EasyPagination\Interfaces\StartSizeDataInterface
     */
    private function createStartSizeData(StartSizeConfigInterface $config, $data): StartSizeDataInterface
    {
        if (\is_array($data) === false) {
            return new StartSizeData($config->getStartDefault(), $config->getSizeDefault());
        }

        $start = empty($data[$config->getStartAttribute()])
            ? $config->getStartDefault()
            : (int)$data[$config->getStartAttribute()];

        $size = empty($data[$config->getSizeAttribute()])
            ? $config->getSizeDefault()
            : (int)$data[$config->getSizeAttribute()];

        return new StartSizeData($start, $size);
    }
}


