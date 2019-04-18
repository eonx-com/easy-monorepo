<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPagination\Traits;

use StepTheFkUp\EasyPagination\Data\StartSizeData;
use StepTheFkUp\EasyPagination\Interfaces\StartSizeConfigInterface;
use StepTheFkUp\EasyPagination\Interfaces\StartSizeDataInterface;

trait DataResolverTrait
{
    /**
     * Create page pagination data for given data and configuration.
     *
     * @param \StepTheFkUp\EasyPagination\Interfaces\StartSizeConfigInterface $config
     * @param mixed $data
     *
     * @return \StepTheFkUp\EasyPagination\Interfaces\StartSizeDataInterface
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
    'LoyaltyCorp\EasyPagination\Traits\DataResolverTrait',
    false
);
