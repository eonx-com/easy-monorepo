<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Traits;

use StepTheFkUp\Pagination\Data\PagePaginationData;
use StepTheFkUp\Pagination\Interfaces\PagePaginationDataInterface;
use StepTheFkUp\Pagination\Resolvers\Config\PagePaginationConfig;

trait PagePaginationDataResolverTrait
{
    /**
     * Create page pagination data for given data and configuration.
     *
     * @param \StepTheFkUp\Pagination\Resolvers\Config\PagePaginationConfig $config
     * @param mixed $data
     *
     * @return \StepTheFkUp\Pagination\Interfaces\PagePaginationDataInterface
     */
    private function createPagePaginationData(PagePaginationConfig $config, $data): PagePaginationDataInterface
    {
        if (\is_array($data) === false) {
            return new PagePaginationData($config->getNumberDefault(), $config->getSizeDefault());
        }

        return new PagePaginationData(
            empty($data[$config->getNumberAttr()]) ? $config->getNumberDefault() : $data[$config->getNumberAttr()],
            empty($data[$config->getSizeAttr()]) ? $config->getSizeDefault() : $data[$config->getSizeAttr()]
        );
    }
}