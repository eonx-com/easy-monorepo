<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPagination\Traits;

use LoyaltyCorp\EasyPagination\Data\StartSizeData;
use LoyaltyCorp\EasyPagination\Interfaces\StartSizeConfigInterface;
use LoyaltyCorp\EasyPagination\Interfaces\StartSizeDataInterface;

trait DataResolverTrait
{
    /**
     * Create page pagination data for given data and configuration.
     *
     * @param \LoyaltyCorp\EasyPagination\Interfaces\StartSizeConfigInterface $config
     * @param mixed $data
     *
     * @return \LoyaltyCorp\EasyPagination\Interfaces\StartSizeDataInterface
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

\class_alias(
    DataResolverTrait::class,
    'StepTheFkUp\EasyPagination\Traits\DataResolverTrait',
    false
);
