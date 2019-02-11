<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Traits;

use StepTheFkUp\Pagination\Data\StartSizeData;
use StepTheFkUp\Pagination\Interfaces\StartSizeConfigInterface;
use StepTheFkUp\Pagination\Interfaces\StartSizeDataInterface;

trait PagePaginationDataResolverTrait
{
    /**
     * Create page pagination data for given data and configuration.
     *
     * @param \StepTheFkUp\Pagination\Interfaces\StartSizeConfigInterface $config
     * @param mixed[] $data
     *
     * @return \StepTheFkUp\Pagination\Interfaces\StartSizeDataInterface
     */
    private function createPagePaginationData(StartSizeConfigInterface $config, $data): StartSizeDataInterface
    {
        if (\is_array($data) === false) {
            return new StartSizeData($config->getStartDefault(), $config->getSizeDefault());
        }

        return new StartSizeData(
            empty($data[$config->getStartAttribute()]) ? $config->getStartDefault() : (int)$data[$config->getStartAttribute()],
            empty($data[$config->getSizeAttribute()]) ? $config->getSizeDefault() : (int)$data[$config->getSizeAttribute()]
        );
    }
}